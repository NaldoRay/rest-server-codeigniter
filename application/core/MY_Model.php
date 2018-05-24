<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property MY_Loader $load
 */
class MY_Model extends CI_Model
{
    /** for insert/update it's recommended to use $this->getFieldMap() e.g. ['field1' => 'table_field1', 'field2' => 'table_field2'] */
    protected $fieldMap = [];

    /** for write-only fields, override to replace, use {@link addWriteOnlyFieldMap} to add into existing */
    protected $writeOnlyFieldMap = [];
    /** for view/read-only fields */
    protected $readOnlyFieldMap = [];
    /** for sort-only fields, not readable/writable (hidden) */
    protected $sortOnlyFieldMap = [];

    /** prefix from column name with boolean data type, for auto-convert */
    protected $booleanPrefixes = [];
    /** prefix from column name with number data type (integer, float), for auto-convert */
    protected $numberPrefixes = [];
    /** prefix from column name with date & time data type (datetime, timestamp), for auto-convert */
    protected $dateTimePrefixes = [];

    /** used as default when no sorts param given when calling get method e.g. ['field1', 'field2'] */
    protected $defaultSorts = [];

    /** will be displayed on validation error result */
    protected $domain = 'API';

    /** each model has its own validation instance (states) */
    protected $validation;

    /** @var DbManager */
    private static $dbManager;
    /** @var CI_DB_driver|CI_DB_query_builder  */
    private $db;


    public function __construct ($connectionName = '')
    {
        parent::__construct();

        $this->validation = new Rest_validation();
        $this->validation->setDomain($this->domain);

        if (!isset(self::$dbManager))
            self::$dbManager = new DbManager();

        $this->db = self::$dbManager->getDb($connectionName);
    }

    protected function getDb ()
    {
        return $this->db;
    }

    protected final function getNextId ($table, $field, $padLength = 0, array $filters = null)
    {
        $entity = $this->doGetFirstEntity($table, $filters,
            [$field], ['-' . $field]
        );
        return $this->getNextEntityId($entity, $field, $padLength);
    }

    private function getNextEntityId ($entity, $field, $padLength = 0)
    {
        if (is_null($entity))
            $lastId = 0;
        else
            $lastId = (int) $entity->$field;

        $nextId = $lastId + 1;
        if ($padLength > 0)
            return str_pad($nextId, $padLength, '0', STR_PAD_LEFT);
        else
            return (string) $nextId;
    }

    /**
     * @param Closure $closure
     * @return mixed
     * @throws Exception
     */
    protected function doTransaction (Closure $closure)
    {
        self::$dbManager->startTransaction();

        try
        {
            $result = $closure();

            if (self::$dbManager->isTransactionSuccess())
                self::$dbManager->commitTransaction();
            else
                throw new TransactionException('Transaction failed', $this->domain);

            return $result;
        }
        catch (Exception $e)
        {
            self::$dbManager->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * @param $table
     * @param array $data entity's field => value
     * @param array $filters entity's filter field => filter value
     * @param array|null $allowedFields entity's fields
     * @return object
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function createOrUpdateEntity ($table, array $data, array $filters, array $allowedFields = null)
    {
        if ($this->entityExists($table, $filters))
        {
            return $this->updateEntity($table, $data, $filters, $allowedFields);
        }
        else
        {
            return $this->createEntity($table, $data, $allowedFields);
        }
    }

    /**
     * @param string $table
     * @param array $dataArr array of entity data entity's field => value
     * @param array|null $allowedFields
     * @return int number of entities created
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function createEntities ($table, array $dataArr, array $allowedFields = null)
    {
        if (empty($dataArr))
            throw new TransactionException(sprintf('Failed to create %s, empty data', $this->domain), $this->domain);

        foreach ($dataArr as $idx => $data)
            $dataArr[ $idx ] = $this->toWriteTableData($data, $allowedFields);

        $count = $this->db->insert_batch($table, $dataArr);
        if ($count === false)
            return 0;
        else
            return $count;
    }

    /**
     * @param string $table
     * @param array $data entity's field => value
     * @param array|null $allowedFields entity's fields
     * @return object
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function createEntity ($table, array $data, array $allowedFields = null)
    {
        $data = $this->toWriteTableData($data, $allowedFields);
        $success = $this->insertRow($table, $data);
        if ($success)
            return $this->toEntity($data);
        else
            throw new TransactionException(sprintf('Failed to create %s', $this->domain), $this->domain);
    }

    /**
     * @param string $table
     * @param array $data table's field => value
     * @return bool
     */
    protected function insertRow ($table, array $data)
    {
        if (empty($data))
            return false;
        else
            return $this->db->insert($table, $data);
    }

    /**
     * @param string $table
     * @param array $dataArr array of entity data entity's field => value
     * @param string $indexField
     * @param array|null $allowedFields entity's fields
     * @return int number of entities updated
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function updateEntities ($table, array $dataArr, $indexField, array $allowedFields = null)
    {
        if (empty($dataArr))
            throw new TransactionException(sprintf('Failed to update %s, empty data', $this->domain), $this->domain);

        foreach ($dataArr as $idx => $data)
            $dataArr[ $idx ] = $this->toWriteTableData($data, $allowedFields);

        $fieldMap = $this->getWriteFieldMap();
        if (isset($fieldMap[$indexField]))
        {
            $indexField = $fieldMap[$indexField];

            $count = $this->db->update_batch($table, $dataArr, $indexField);
            if ($count !== false)
                return $count;
        }

        return 0;
    }

    /**
     * @param string $table
     * @param array $data entity's field => value
     * @param QueryCondition $condition
     * @param array|null $allowedFields entity's fields
     * @return object entity with updated fields on success
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function updateEntityWithCondition ($table, array $data, QueryCondition $condition, array $allowedFields = null)
    {
        $condition = $this->toTableCondition($condition);
        if (!is_null($condition))
            $this->db->where($condition->getConditionString());

        return $this->updateEntity($table, $data, array(), $allowedFields);
    }

    /**
     * @param string $table
     * @param array $data entity's field => value
     * @param array $filters entity's filter field => filter value
     * @param array|null $allowedFields entity's fields
     * @return object entity with updated fields on success
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function updateEntity ($table, array $data, array $filters, array $allowedFields = null)
    {
        $filters = $this->getTableFilters($filters);
        $data = $this->toWriteTableData($data, $allowedFields);

        $success = $this->updateRow($table, $data, $filters);
        if ($success)
        {
            if ($this->db->affected_rows() > 0)
                return $this->toEntity($data);
            else
                throw new ResourceNotFoundException(sprintf('%s not found', $this->domain), $this->domain);
        }
        else
        {
            throw new TransactionException(sprintf('Failed to update %s', $this->domain), $this->domain);
        }
    }

    /**
     * @param string $table
     * @param array $data table's field => value
     * @param array $filters table's filter field => filter value
     * @return bool
     */
    protected function updateRow ($table, array $data, array $filters)
    {
        if (empty($data))
        {
            return false;
        }
        else
        {
            $this->setQueryFilters($filters);
            return $this->db->update($table, $data);
        }
    }

    /**
     * @param string $table
     * @param QueryCondition $condition
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException if delete failed because of database error
     */
    protected function deleteEntityWithCondition ($table, QueryCondition $condition)
    {
        $condition = $this->toTableCondition($condition);
        if (!is_null($condition))
            $this->db->where($condition->getConditionString());

        $this->deleteEntity($table, array());
    }

    /**
     * @param string $table
     * @param array $filters entity's filter field => filter value
     * @throws ResourceNotFoundException
     * @throws TransactionException if delete failed because of database error
     */
    protected function deleteEntity ($table, array $filters)
    {
        $filters = $this->getTableFilters($filters);

        $this->setQueryFilters($filters);

        $result = $this->db->delete($table);
        if ($result === false)
        {
            throw new TransactionException(sprintf('Failed to delete %s', $this->domain), $this->domain);
        }
        else
        {
            if ($this->db->affected_rows() == 0)
                throw new ResourceNotFoundException(sprintf('%s not found', $this->domain), $this->domain);
        }
    }

    /**
     * Use this only for query that does not return result set e.g. create, update, delete.
     * @param string $sql
     * @return bool
     */
    protected function executeRawQuery ($sql)
    {
        return ($this->db->query($sql) !== false);
    }

    /**
     * @param string $sql
     * @return object[]
     */
    protected function getAllEntitiesFromRawQuery ($sql)
    {
        /** @var CI_DB_result $result */
        $result = $this->db->query($sql, false, true);
        $rows = $result->result_array();

        // free the memory associated with the result and deletes the result resource ID.
        $result->free_result();
        return $this->toEntities($rows);
    }

    /**
     * @param string $table
     * @param array $filters entity's filter field => filter value, e.g. ['id' => 1]
     * @param array $fields entity's fields
     * @return null|object
     */
    protected function getEntity ($table, array $filters, array $fields = null)
    {
        $filters = $this->getTableFilters($filters);
        $fields = $this->getTableFields($fields);

        $row = $this->getRow($table, $filters, $fields);
        if (is_null($row))
            return null;
        else
            return $this->toEntity($row);
    }

    /**
     * @param string $table
     * @param array $filters table's filter field => filter value
     * @param array $fields table's fields
     * @return array|null
     */
    private function getRow ($table, array $filters, array $fields = null)
    {
        $this->setQueryFilters($filters);
        $this->setQueryFields($fields);

        $result = $this->db->limit(1)
            ->get($table);

        return $result->row_array();
    }

    /**
     * @param string $table
     * @param array|null $filters
     * @param array|null $fields
     * @param array|null $sorts
     * @return null|object
     */
    protected function getFirstEntity ($table, array $filters = null, array $fields = null, array $sorts = null)
    {
        return $this->doGetFirstEntity($table, $filters, $fields, $sorts);
    }

    private function doGetFirstEntity ($table, array $filters = null, array $fields = null, array $sorts = null)
    {
        $filters = $this->getTableFilters($filters);
        $fields = $this->getTableFields($fields);
        $sorts = $this->getTableSorts($sorts);

        $row = $this->getFirstRow($table, $filters, $fields, $sorts);
        if (is_null($row))
            return null;
        else
            return $this->toEntity($row);
    }

    /**
     * @param string $table
     * @param array $filters
     * @param array $fields table's fields
     * @param array $sorts table's sort fields
     * @return array|null
     */
    private function getFirstRow ($table, array $filters = null, array $fields = null, array $sorts = null)
    {
        $this->setQueryFilters($filters);
        $this->setQueryFields($fields);
        $this->setQuerySorts($sorts, $fields);

        $result = $this->db->limit(1)
            ->get($table);

        return $result->row_array();
    }

    /**
     * @param string $table
     * @param QueryParam $param
     * @param array $fields
     * @param bool $distinct
     * @return object[]
     * @throws BadFormatException
     */
    protected function getAllEntities ($table, QueryParam $param = null, array $fields = null, $distinct = false)
    {
        if (is_null($param))
            $param = QueryParam::create();

        $condition = $param->getCondition();
        $sorts = $param->getSorts();
        $limit = $param->getLimit();
        $offset = $param->getOffset();

        if (!is_null($condition))
            $condition = $this->toTableCondition($condition);
        $fields = $this->getTableFields($fields);
        $sorts = $this->getTableSorts($sorts);

        // SQL doesn't allow ORDER BY on field that is not selected on DISTINCT.
        // If this is a distinct query and there's a hidden read-only field in sorts,
        // then we also need to select that field and hide it on the result
        if ($distinct && !empty($sorts) && !empty($this->sortOnlyFieldMap))
        {
            $tableSortFields = array_map(function ($sort)
            {
                return explode(' ', $sort)[0];
            }, $sorts);
            $hiddenSortFields = array_intersect($tableSortFields, array_values($this->sortOnlyFieldMap));

            if (!empty($hiddenSortFields))
            {
                if (empty($fields))
                    $fields = array_values($this->getReadFieldMap());

                $fields = array_merge($fields, $hiddenSortFields);
            }
        }

        $rows = $this->getAllRows($table, $condition, $fields, $distinct, $sorts, $limit, $offset);
        return $this->toEntities($rows);
    }

    /**
     * Converts condition's field/value to table's field/value, and escape identifiers & values.
     * @param QueryCondition $condition
     * @return QueryCondition null if field is not found
     * @throws BadFormatException
     */
    private function toTableCondition (QueryCondition $condition)
    {
        if ($condition instanceof LogicalCondition)
        {
            $condition = clone $condition;

            $tableConditions = array();
            $queryConditions = $condition->getConditions();
            foreach ($queryConditions as $queryCondition)
            {
                $tableCondition = $this->toTableCondition($queryCondition);
                if (!is_null($tableCondition))
                    $tableConditions[] = $tableCondition;
            }

            if (empty($tableConditions))
            {
                return null;
            }
            else
            {
                $condition->setConditions($tableConditions);
                return $condition;
            }
        }
        else if ($condition instanceof FieldValueCondition)
        {
            $condition = clone $condition;

            $field = $condition->getField();
            $fieldMap = $this->getFullReadFieldMap();
            if (isset($fieldMap[ $field ]))
            {
                $field = $fieldMap[ $field ];
                $value = $condition->getValue();
                if (is_array($value))
                {
                    $values = array();
                    foreach ($value as $val)
                    {
                        $val = $this->toTableValue($field, $val);
                        $values[] = $this->db->escape($val);
                    }

                    $value = $values;
                }
                else
                {
                    $value = $this->toTableValue($field, $value);
                    $value = $this->db->escape($value);
                }
                $condition->setFieldValue($field, $value);

                return $condition;
            }
            else
            {
                return null;
            }
        }
        else
        {
            return null;
        }
    }

    /**
     * @param string $table
     * @param QueryCondition $condition
     * @param array $fields table's fields
     * @param bool $distinct
     * @param array $sorts table's sort fields
     * @param int $limit
     * @param int $offset
     * @return array
     */
    private function getAllRows ($table, QueryCondition $condition = null, array $fields = null, $distinct = false, array $sorts = null, $limit = -1, $offset = 0)
    {
        if (!is_null($condition))
            $this->db->where($condition->getConditionString());

        $this->setQueryFields($fields);
        $this->setQuerySorts($sorts, $fields);
        $this->setQueryDistinct($distinct);
        $this->setQueryLimit($limit, $offset);

        $result = $this->db->get($table);
        return $result->result_array();
    }

    /**
     * @param $table
     * @param array $filters entity's filter field => filter value
     * @return bool
     */
    protected function entityExists ($table, array $filters = null)
    {
        $filters = $this->getTableFilters($filters);

        return $this->rowExists($table, $filters);
    }

    /**
     * @param string $table
     * @param QueryCondition $condition
     * @return bool
     */
    protected function entityExistsWithCondition ($table, QueryCondition $condition)
    {
        $this->db->where($condition->getConditionString());

        return $this->rowExists($table);
    }

    /**
     * @param $table
     * @param array $filters table's filter field => filter value
     * @return bool
     */
    protected function rowExists ($table, array $filters = null)
    {
        $this->setQueryFilters($filters);

        $result = $this->db->select('1')
            ->limit(1)
            ->get($table);

        return ($result->num_rows() > 0);
    }

    /**
     * @param array $fields
     */
    private function setQueryFields (array $fields = null)
    {
        if (!empty($fields))
        {
            $this->db->select(implode(',', $fields));
        }
    }

    /**
     * @param array $filters
     */
    private function setQueryFilters (array $filters = null)
    {
        if (!empty($filters))
        {
            foreach ($filters as $field => $value)
            {
                if (is_array($value))
                    $this->db->where_in($field, $value);
                else
                    $this->db->where($field, $value);
            }
        }
    }

    /**
     * @param array $sorts
     * @param array $fields
     */
    private function setQuerySorts (array $sorts = null, array $fields = null)
    {
        if (!empty($sorts))
        {
            if (!empty($this->sortOnlyFieldMap))
            {
                if (empty($fields))
                    $fields = array_values($this->getFullReadFieldMap());
                else
                    $fields = array_merge($fields, array_values($this->sortOnlyFieldMap));
            }

            if (!empty($fields))
            {
                $sorts = array_filter($sorts, function ($sort) use ($fields)
                {
                    $sortField = explode(' ', $sort)[0];
                    return in_array($sortField, $fields);
                });
            }
            $this->db->order_by(implode(',', $sorts));
        }
    }

    /**
     * @param bool $distinct
     */
    private function setQueryDistinct ($distinct = false)
    {
        $this->db->distinct((bool)$distinct);
    }

    /**
     * @param int $limit
     * @param int $offset
     */
    private function setQueryLimit ($limit = -1, $offset = 0)
    {
        if ($limit >= 0 || $offset > 0)
        {
            if ($limit < 0)
                $limit = PHP_INT_MAX;
            if ($offset < 0)
                $offset = 0;

            $this->db->limit($limit, $offset);
        }
    }

    /**
     * Filters and maps all fields & values to table format.
     * @param array $data entity's field => value
     * @param array $allowedFields entity's fields
     * @return array
     * @throws BadFormatException
     */
    protected function toWriteTableData (array $data, array $allowedFields = null)
    {
        if (empty($data))
            return array();

        $fieldMap = $this->getWriteFieldMap();
        // default is to allow all fields
        if (empty($allowedFields))
            $allowedFields = array_keys($fieldMap);
        else
            $allowedFields = array_merge($allowedFields, array_keys($this->writeOnlyFieldMap));

        $dataFields = array_keys($data);
        $writeFields = array_intersect($dataFields, $allowedFields);

        $tableData = array();
        foreach ($writeFields as $field)
        {
            $tableField = $fieldMap[$field];
            $tableValue = $data[$field];
            if (!is_null($tableValue))
                $tableValue = $this->toTableValue($tableField, $tableValue);

            $tableData[$tableField] = $tableValue;
        }
        return $tableData;
    }

    /**
     * Maps all filters to table format.
     * @param array $filters entity's filter field => filter value
     * @return array
     */
    protected function getTableFilters (array $filters = null)
    {
        if (empty($filters))
            return array();

        $filterData = array();

        $fieldMap = $this->getFullReadFieldMap();
        foreach ($filters as $field => $value)
        {
            if (isset($fieldMap[$field]))
            {
                $field = $fieldMap[$field];
                try
                {
                    $value = $this->toTableValue($field, $value);
                    $filterData[$field] = $value;
                }
                catch (BadFormatException $e)
                {}
            }
        }

        return $filterData;
    }

    /**
     * @param string $field table's field
     * @param mixed $value
     * @return bool|string
     * @throws BadFormatException
     */
    private function toTableValue ($field, $value)
    {
        if ($this->isBooleanField($field))
        {
            // set field only if it has valid value
            $value = $this->tryParseBoolean($value);
            return ($value ? '1' : '0');
        }
        else if ($this->isNumberField($field))
        {
            return $this->tryParseNumber($value);
        }
        else if ($this->isDateTimeField($field))
        {
            // convert valid ISO-8601 date & time to local timezone
            if ($this->isIso8601DateTime($value))
                $value = date('Y-m-d H:i:s', strtotime($value));

            return $value;
        }
        else
        {
            if (is_array($value))
            {
                foreach ($value as $idx => $val)
                    $value[$idx] = $this->toTableValue($field, $val);
            }

            return $value;
        }
    }

    /**
     * Maps all sort fields to table sort fields
     * @param array $sorts entity's sort fields, eg. ['field1', '-field2']
     * @return array
     */
    protected function getTableSorts (array $sorts = null)
    {
        if (empty($sorts))
        {
            $sorts = $this->defaultSorts;
            if (empty($sorts))
                return array();
        }

        $sortData = array();
        $fieldMap = $this->getFullReadFieldMap();
        foreach ($sorts as $sort)
        {
            if ($sort[0] === '-')
            {
                $field = substr($sort, 1);
                $order = 'DESC';
            }
            else
            {
                $field = $sort;
                $order = 'ASC';
            }

            if (isset($fieldMap[$field]))
            {
                $sortData[] = sprintf('%s %s', $fieldMap[$field], $order);
            }
        }
        return $sortData;
    }

    /**
     * Maps all fields to table fields.
     * @param array $fields entity's fields, eg. ['field1', 'field2']
     * @return array
     */
    protected function getTableFields (array $fields = null)
    {
        $fieldMap = $this->getReadFieldMap();

        if (empty($fields))
        {
            if (empty($fieldMap))
                return array();
            else
                return array_values($fieldMap);
        }
        else
        {
            $tableFields = array();
            foreach ($fields as $field)
            {
                if (isset($fieldMap[ $field ]))
                    $tableFields[] = $fieldMap[ $field ];
            }
            return $tableFields;
        }
    }

    /**
     * @param array $rows table rows
     * @return object[]
     */
    protected function toEntities (array $rows)
    {
        if (empty($rows))
            return array();

        return array_map(function($row){
            if (is_null($row))
                return null;
            else
                return $this->toEntity($row);
        }, $rows);
    }

    /**
     * @param array $row table row
     * @return object
     */
    protected function toEntity (array $row)
    {
        // using `limit 1` on oracle db / oci8 will add RNUM field
        // need to be removed  manually
        unset($row['RNUM']);

        foreach ($this->writeOnlyFieldMap as $tableField)
            unset($row[ $tableField ]);

        foreach ($this->sortOnlyFieldMap as $tableField)
            unset($row[ $tableField ]);

        $entity = new stdClass();
        $fieldMap = array_flip($this->getReadFieldMap());
        foreach ($row as $field => $value)
        {
            if ($this->isBooleanField($field))
            {
                $value = (bool)$value;
            }
            else if ($this->isDateTimeField($field))
            {
                if ($this->isIso8601DateTime($value))
                    $value = date(DateTime::ISO8601, strtotime($value));
            }

            if (isset($fieldMap[$field]))
                $field = $fieldMap[$field];

            $entity->{$field} = $value;
        }
        return $entity;
    }

    private function isBooleanField ($field)
    {
        foreach ($this->booleanPrefixes as $prefix)
        {
            if (strpos($field, $prefix) === 0)
                return true;
        }
        return false;
    }

    /**
     * @param mixed $value
     * @return bool
     * @throws BadFormatException if value type is not a boolean or not one of boolean strings: 'true', 'false', '0', '1')
     */
    protected function tryParseBoolean ($value)
    {
        if ($value === 'true' || $value === 'false' || $value === '0' || $value === '1')
            $value = ($value === 'true' || $value === '1');

        if (is_bool($value))
            return $value;
        else
            throw new BadFormatException(sprintf('%s is not boolean or boolean string', $value), $this->domain);
    }

    private function isNumberField ($field)
    {
        foreach ($this->numberPrefixes as $prefix)
        {
            if (strpos($field, $prefix) === 0)
                return true;
        }
        return false;
    }

    /**
     * @param mixed $value
     * @return bool
     * @throws BadFormatException if value type is not a number or numeric string
     */
    protected function tryParseNumber ($value)
    {
        if (is_numeric($value))
            return $value + 0;
        else
            throw new BadFormatException(sprintf('%s is not a number or numeric string', $value), $this->domain);
    }

    private function isDateTimeField ($field)
    {
        foreach ($this->dateTimePrefixes as $prefix)
        {
            if (strpos($field, $prefix) === 0)
            {
                return true;
            }
        }
        return false;
    }

    private function isIso8601DateTime ($value)
    {
        return $this->validation->forValue($value)
            ->nullable()
            ->notEmpty()
            ->validDateTime()
            ->validate();
    }

    protected final function addWriteOnlyFieldMap (array $fieldMap)
    {
        $this->writeOnlyFieldMap = array_merge($this->writeOnlyFieldMap, $fieldMap);
    }

    protected function getWriteFieldMap ()
    {
        return array_merge($this->fieldMap, $this->writeOnlyFieldMap);
    }

    /**
     * @return array result of getReadFieldMap() file including hidden read-only field map
     */
    private function getFullReadFieldMap ()
    {
        if (empty($this->sortOnlyFieldMap))
            return $this->getReadFieldMap();
        else
            return array_merge($this->getReadFieldMap(), $this->sortOnlyFieldMap);
    }

    private function getReadFieldMap ()
    {
        if (empty($this->readOnlyFieldMap))
            return $this->fieldMap;
        else
            return array_merge($this->fieldMap, $this->readOnlyFieldMap);
    }

    protected final function limitFields (array $fields, array $allowedFields = [])
    {
        // empty fields means select all, limit to select only allowed fields
        if (empty($fields))
            return $allowedFields;

        // default is to allow all read fields
        if (empty($allowedFields))
            $allowedFields = array_keys($this->getReadFieldMap());

        return array_intersect($fields, $allowedFields);
    }

    /**
     * @param $leftEntity
     * @param Closure $getRightEntity returns the right entity for join, throws ResourceNotFoundException if entity is not found
     * @param array $fields
     */
    protected function join ($leftEntity, Closure $getRightEntity, array $fields)
    {
        try
        {
            $rightEntity = $getRightEntity();
            $this->leftJoin($leftEntity, $rightEntity, $fields);
        }
        catch (ResourceNotFoundException $e)
        {
            foreach ($fields as $field)
            {
                if (!property_exists($leftEntity, $field))
                    $leftEntity->$field = null;
            }
        }
    }

    /**
     * @param object $leftEntity
     * @param object $rightEntity
     * @param array|null $fields
     */
    private function leftJoin ($leftEntity, $rightEntity, array $fields = null)
    {
        if (empty($fields))
            $fields = array_keys((array)$rightEntity);

        foreach ($fields as $field)
        {
            // only set property if not exists on leftEntity
            if (!property_exists($leftEntity, $field))
                $leftEntity->$field = $rightEntity->$field;
        }
    }
}
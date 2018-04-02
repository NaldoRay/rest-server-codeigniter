<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class MY_Model extends CI_Model
{
    /** for insert/update it's recommended to use $this->getFieldMap() e.g. ['field1' => 'table_field1', 'field2' => 'table_field2'] */
    protected $fieldMap = [];

    /** for write-only fields */
    protected $writeOnlyFieldMap = [];
    /** for view/read-only fields */
    protected $readOnlyFieldMap = [];
    /** for sorting-only fields, not used on read/write */
    protected $hiddenReadOnlyFieldMap = [];

    /** prefix from column name with boolean type, for auto-convert */
    protected $booleanPrefixes = [];
    /** prefix from column name with number type (integer, float), for auto-convert */
    protected $numberPrefixes = [];

    /** used as default when no sorts param given when calling get method e.g. ['field1', 'field2'] */
    protected $defaultSorts = [];

    /** will be displayed on validation error result */
    protected $domain = 'API';

    /** each model has its own validation instance (states) */
    protected $validation;

    /** @var DbManager */
    private static $dbManager;


    public function __construct ()
    {
        parent::__construct();

        $this->validation = new Rest_validation();
        $this->validation->setDomain($this->domain);

        if (!isset(self::$dbManager))
            self::$dbManager = new DbManager();
    }

    protected function getDb ($name)
    {
        return self::$dbManager->getDb($name);
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
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $sql
     * @return object[]
     */
    protected function getAllEntitiesFromRawQuery ($db, $sql)
    {
        /** @var CI_DB_result $result */
        $result = $db->query($sql, false, true);
        $rows = $result->result_array();

        // free the memory associated with the result and deletes the result resource ID.
        $result->free_result();
        return $this->toEntities($rows);
    }

    /**
     * Use this only for query that does not return result set e.g. create, update, delete.
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $sql
     * @return bool
     */
    protected function executeRawQuery ($db, $sql)
    {
        return ($db->query($sql) !== false);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param $table
     * @param array $data entity's field => value
     * @param array $filters entity's filter field => filter value
     * @param array|null $allowedFields entity's fields
     * @return object
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function createOrUpdateEntity ($db, $table, array $data, array $filters, array $allowedFields = null)
    {
        if ($this->entityExists($db, $table, $filters))
        {
            return $this->updateEntity($db, $table, $data, $filters, $allowedFields);
        }
        else
        {
            return $this->createEntity($db, $table, $data, $allowedFields);
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $dataArr array of entity data entity's field => value
     * @param array|null $allowedFields
     * @return int number of entities created
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function createEntities ($db, $table, array $dataArr, array $allowedFields = null)
    {
        if (empty($dataArr))
            throw new TransactionException(sprintf('Failed to create %s, empty data', $this->domain), $this->domain);

        foreach ($dataArr as $idx => $data)
            $dataArr[ $idx ] = $this->toWriteTableData($data, $allowedFields);

        $count = $db->insert_batch($table, $dataArr);
        if ($count === false)
            return 0;
        else
            return $count;
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $data entity's field => value
     * @param array|null $allowedFields entity's fields
     * @return object
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function createEntity ($db, $table, array $data, array $allowedFields = null)
    {
        $data = $this->toWriteTableData($data, $allowedFields);
        $success = $this->insertRow($db, $table, $data);
        if ($success)
            return $this->toEntity($data);
        else
            throw new TransactionException(sprintf('Failed to create %s', $this->domain), $this->domain);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $data table's field => value
     * @return bool
     */
    protected function insertRow ($db, $table, array $data)
    {
        if (empty($data))
            return false;
        else
            return $db->insert($table, $data);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $dataArr array of entity data entity's field => value
     * @param string $indexField
     * @param array|null $allowedFields entity's fields
     * @return int number of entities updated
     * @throws BadFormatException
     * @throws TransactionException
     */
    protected function updateEntities ($db, $table, array $dataArr, $indexField, array $allowedFields = null)
    {
        if (empty($dataArr))
            throw new TransactionException(sprintf('Failed to update %s, empty data', $this->domain), $this->domain);

        foreach ($dataArr as $idx => $data)
            $dataArr[ $idx ] = $this->toWriteTableData($data, $allowedFields);

        $fieldMap = $this->getWriteFieldMap();
        if (isset($fieldMap[$indexField]))
        {
            $indexField = $fieldMap[$indexField];

            $count = $db->update_batch($table, $dataArr, $indexField);
            if ($count !== false)
                return $count;
        }

        return 0;
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $data entity's field => value
     * @param array $filters entity's filter field => filter value
     * @param array|null $allowedFields entity's fields
     * @return object entity with updated fields on success
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function updateEntity ($db, $table, array $data, array $filters, array $allowedFields = null)
    {
        $filters = $this->toTableFilters($filters);
        $data = $this->toWriteTableData($data, $allowedFields);

        $success = $this->updateRow($db, $table, $data, $filters);
        if ($success)
        {
            if ($db->affected_rows() > 0)
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
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $data entity's field => value
     * @param QueryCondition $condition
     * @param array|null $allowedFields entity's fields
     * @return object entity with updated fields on success
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function updateEntityWithCondition ($db, $table, array $data, QueryCondition $condition, array $allowedFields = null)
    {
        $condition = $this->toTableCondition($db, $condition);
        if (!is_null($condition))
            $db->where($condition->getConditionString());

        return $this->updateEntity($db, $table, $data, array(), $allowedFields);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $data table's field => value
     * @param array $filters table's filter field => filter value
     * @return bool
     */
    protected function updateRow ($db, $table, array $data, array $filters)
    {
        if (empty($data))
        {
            return false;
        }
        else
        {
            $this->setQueryFilters($db, $filters);
            return $db->update($table, $data);
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $filters entity's filter field => filter value
     * @throws ResourceNotFoundException
     * @throws TransactionException if delete failed because of database error
     */
    protected function deleteEntity ($db, $table, array $filters)
    {
        $filters = $this->toTableFilters($filters);

        $result = $db->delete($table, $filters);
        if ($result === false)
        {
            throw new TransactionException(sprintf('Failed to delete %s', $this->domain), $this->domain);
        }
        else
        {
            if ($db->affected_rows() == 0)
                throw new ResourceNotFoundException(sprintf('%s not found', $this->domain), $this->domain);
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param QueryCondition $condition
     * @throws BadFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException if delete failed because of database error
     */
    protected function deleteEntityWithCondition ($db, $table, QueryCondition $condition)
    {
        $condition = $this->toTableCondition($db, $condition);
        if (!is_null($condition))
            $db->where($condition->getConditionString());

        $result = $db->delete($table);

        if ($result === false)
        {
            throw new TransactionException(sprintf('Failed to delete %s', $this->domain), $this->domain);
        }
        else
        {
            if ($db->affected_rows() == 0)
                throw new ResourceNotFoundException(sprintf('%s not found', $this->domain), $this->domain);
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $filters entity's filter field => filter value, e.g. ['id' => 1]
     * @param array $fields entity's fields
     * @return object|null
     */
    protected function getEntity ($db, $table, array $filters, array $fields = null)
    {
        if (!empty($filters))
            $filters = $this->toTableFilters($filters);
        if (!empty($fields))
            $fields = $this->toTableFields($fields);

        $row = $this->getRow($db, $table, $filters, $fields);
        if (is_null($row))
            return null;
        else
            return $this->toEntity($row);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param QueryCondition $condition
     * @param array $fields entity's fields
     * @return object
     * @throws BadFormatException
     */
    protected function getEntityWithCondition ($db, $table, QueryCondition $condition, array $fields = null)
    {
        if (!empty($fields))
            $fields = $this->toTableFields($fields);

        $condition = $this->toTableCondition($db, $condition);
        if (!is_null($condition))
            $db->where($condition->getConditionString());

        $row = $this->getRow($db, $table, array(), $fields);
        if (is_null($row))
            return null;
        else
            return $this->toEntity($row);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $filters table's filter field => filter value
     * @param array $fields table's fields
     * @return array|null
     */
    private function getRow ($db, $table, array $filters, array $fields = null)
    {
        $this->setQueryFilters($db, $filters);

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->limit(1)
            ->get($table);

        return $result->row_array();
    }

    protected function getFirstEntity ($db, $table, array $filters = null, array $searches = null, array $fields = null, array $sorts = null)
    {
        if (!empty($filters))
            $filters = $this->toTableFilters($filters);
        if (!empty($searches))
            $searches = $this->toTableFilters($searches);
        if (!empty($fields))
            $fields = $this->toTableFields($fields);
        $sorts = $this->toTableSortData($sorts);

        $row = $this->getFirstRow($db, $table, $filters, $searches, $fields, $sorts);
        if (is_null($row))
            return null;
        else
            return $this->toEntity($row);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $filters table's filter field => filter value
     * @param array $searches table's search field => search value
     * @param array $fields table's fields
     * @param array $sorts table's sort fields
     * @return array|null
     */
    protected function getFirstRow ($db, $table, array $filters = null, array $searches = null, array $fields = null, array $sorts = null)
    {
        $this->setQueryFilters($db, $filters);
        $this->setQuerySearches($db, $searches);
        $this->setQuerySorts($db, $sorts, $fields);

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->limit(1)
            ->get($table);

        return $result->row_array();
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $filters entity's filter field => filter value
     * @param array|null $searches entity's search field => search value
     * @param array $fields entity's fields
     * @param bool $unique
     * @param array $sorts entity's sort fields
     * @param int $limit
     * @param int $offset
     * @return object[]
     */
    protected function getAllEntities ($db, $table, array $filters = null, array $searches = null, array $fields = null, $unique = false, array $sorts = null, $limit = -1, $offset = 0)
    {
        if (!empty($filters))
            $filters = $this->toTableFilters($filters);
        if (!empty($searches))
            $searches = $this->toTableFilters($searches);
        if (!empty($fields))
            $fields = $this->toTableFields($fields);
        $sorts = $this->toTableSortData($sorts);

        // SQL doesn't allow ORDER BY on field that is not selected on DISTINCT.
        // If this's a distinct query and there's a hidden read-only field in sorts,
        // then we also need to select that field and hide it on the result
        if ($unique && !empty($sorts) && !empty($this->hiddenReadOnlyFieldMap))
        {
            $tableSortFields = array_map(function ($sort)
            {
                return explode(' ', $sort)[0];
            }, $sorts);
            $hiddenSortFields = array_intersect($tableSortFields, array_values($this->hiddenReadOnlyFieldMap));

            if (!empty($hiddenSortFields))
            {
                if (empty($fields))
                    $fields = array_values($this->getReadFieldMap());

                $fields = array_merge($fields, $hiddenSortFields);
            }
        }

        $rows = $this->getAllRows($db, $table, $filters, $searches, $fields, $unique, $sorts, $limit, $offset);
        return $this->toEntities($rows);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param QueryCondition $condition
     * @param array $fields entity's fields
     * @param bool $unique
     * @param array $sorts entity's sort fields
     * @param int $limit
     * @param int $offset
     * @return object[]
     * @throws BadFormatException
     */
    protected function getAllEntitiesWithCondition ($db, $table, QueryCondition $condition, array $fields = null, $unique = false, array $sorts = null, $limit = -1, $offset = 0)
    {
        if (!empty($fields))
            $fields = $this->toTableFields($fields);
        $sorts = $this->toTableSortData($sorts);

        // SQL doesn't allow ORDER BY on field that is not selected on DISTINCT.
        // If unique = true and there's a hidden read-only field in sorts,
        // then we also need to select that field and hide it on the result
        if ($unique && !empty($sorts) && !empty($this->hiddenReadOnlyFieldMap))
        {
            $tableSortFields = array_map(function ($sort)
            {
                return explode(' ', $sort)[0];
            }, $sorts);
            $hiddenSortFields = array_intersect($tableSortFields, array_values($this->hiddenReadOnlyFieldMap));

            if (!empty($hiddenSortFields))
            {
                if (empty($fields))
                    $fields = array_values($this->getReadFieldMap());

                $fields = array_merge($fields, $hiddenSortFields);
            }
        }

        $condition = $this->toTableCondition($db, $condition);
        if (!is_null($condition))
            $db->where($condition->getConditionString());

        $rows = $this->getAllRows($db, $table, null, null, $fields, $unique, $sorts, $limit, $offset);
        return $this->toEntities($rows);
    }

    protected function getFiltersCondition (array $filters)
    {
        if (empty($filters))
            return null;

        $filterConditions = array();
        foreach ($filters as $field => $value)
            $filterConditions[] = new EqualsCondition($field, $value);

        return LogicalCondition::logicalAnd($filterConditions);
    }

    protected function getSearchesCondition (array $searches)
    {
        if (empty($searches))
            return null;

        $filterConditions = array();
        foreach ($searches as $field => $value)
            $filterConditions[] = new ContainsCondition($field, $value, true);

        return LogicalCondition::logicalOr($filterConditions);
    }

    /**
     * Converts condition's field/value to table's field/value, and escape identifiers & values.
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param QueryCondition $condition
     * @return QueryCondition null if field is not found
     * @throws BadFormatException
     */
    private function toTableCondition ($db, QueryCondition $condition)
    {
        if ($condition instanceof LogicalCondition)
        {
            $condition = clone $condition;

            $tableConditions = array();
            $queryConditions = $condition->getConditions();
            foreach ($queryConditions as $queryCondition)
            {
                $tableCondition = $this->toTableCondition($db, $queryCondition);
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
                        $values[] = $db->escape($val);
                    }

                    $value = $values;
                }
                else
                {
                    $value = $this->toTableValue($field, $value);
                    $value = $db->escape($value);
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
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $filters table's filter field => filter value
     * @param array $searches table's search field => search value
     * @param array $fields table's fields
     * @param bool $unique
     * @param array $sorts table's sort fields
     * @param int $limit
     * @param int $offset
     * @return array
     */
    protected function getAllRows ($db, $table, array $filters = null, array $searches = null, array $fields = null, $unique = false, array $sorts = null, $limit = -1, $offset = 0)
    {
        $this->setQueryFilters($db, $filters);
        $this->setQuerySearches($db, $searches);
        $this->setQuerySorts($db, $sorts, $fields);
        $this->setQueryUnique($db, $unique);
        $this->setQueryLimit($db, $limit, $offset);

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->get($table);

        return $result->result_array();
    }

    protected function getSelectField (array $tableFields = null)
    {
        if (empty($tableFields))
        {
            $fieldMap = $this->getReadFieldMap();
            if (empty($fieldMap))
                return '*';
            else
                return array_values($fieldMap);
        }
        else
        {
            return implode(',', $tableFields);
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param $table
     * @param array $filters entity's filter field => filter value
     * @param array $searches entity's search field => search value
     * @return bool
     */
    protected function entityExists ($db, $table, array $filters = null, array $searches = null)
    {
        if (!empty($filters))
            $filters = $this->toTableFilters($filters);
        if (!empty($searches))
            $searches = $this->toTableFilters($searches);

        return $this->rowExists($db, $table, $filters, $searches);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param QueryCondition $condition
     * @return bool
     */
    protected function entityExistsWithCondition ($db, $table, QueryCondition $condition)
    {
        $db->where($condition->getConditionString());

        return $this->rowExists($db, $table);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param $table
     * @param array $filters table's filter field => filter value
     * @param array $searches table's search field => search value
     * @return bool
     */
    protected function rowExists ($db, $table, array $filters = null, array $searches = null)
    {
        $this->setQueryFilters($db, $filters);
        $this->setQuerySearches($db, $searches);

        $result = $db->select('1')
            ->limit(1)
            ->get($table);

        return ($result->num_rows() > 0);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db $db
     * @param array $filters
     */
    private function setQueryFilters ($db, array $filters = null)
    {
        if (!empty($filters))
            $db->where($filters);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db $db
     * @param array $searches
     */
    private function setQuerySearches ($db, array $searches = null)
    {
        if (!empty($searches))
        {
            $searchWhereArr = array();
            foreach ($searches as $field => $search)
            {
                $search = $db->escape($search);
                $searchWhereArr[] = sprintf("LOWER(%s) LIKE ('%%'||LOWER(%s)||'%%')", $field, $search);
            }
            if (!empty($searchWhereArr))
                $db->where(sprintf('(%s)', implode(' OR ', $searchWhereArr)), null, false);
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db $db
     * @param array $sorts
     * @param array $fields
     */
    private function setQuerySorts ($db, array $sorts = null, array $fields = null)
    {
        if (!empty($sorts))
        {
            if (!empty($this->hiddenReadOnlyFieldMap))
            {
                if (empty($fields))
                    $fields = array_values($this->hiddenReadOnlyFieldMap);
                else
                    $fields = array_merge($fields, array_values($this->hiddenReadOnlyFieldMap));
            }

            if (!empty($fields))
            {
                $sorts = array_filter($sorts, function ($sort) use ($fields)
                {
                    $sortField = explode(' ', $sort)[0];
                    return in_array($sortField, $fields);
                });
            }
            $db->order_by(implode(',', $sorts));
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db $db
     * @param bool $unique
     */
    private function setQueryUnique ($db, $unique = false)
    {
        if ($unique)
            $db->distinct();
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db $db
     * @param int $limit
     * @param int $offset
     */
    private function setQueryLimit ($db, $limit = -1, $offset = 0)
    {
        if ($limit >= 0 || $offset > 0)
        {
            if ($limit < 0)
                $limit = PHP_INT_MAX;
            if ($offset < 0)
                $offset = 0;

            $db->limit($limit, $offset);
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
    protected function toTableFilters (array $filters)
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
        else
        {
            return $value;
        }
    }

    /**
     * Maps all sort fields to table sort fields
     * @param array $sorts entity's sort fields, eg. ['field1', '-field2']
     * @return array
     */
    protected function toTableSortData (array $sorts = null)
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
    protected function toTableFields (array $fields)
    {
        if (empty($fields))
            return array();

        $tableFields = array();
        $fieldMap = $this->getReadFieldMap();
        foreach ($fields as $field)
        {
            if (isset($fieldMap[$field]))
                $tableFields[] = $fieldMap[$field];
        }
        return $tableFields;
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

        foreach ($this->hiddenReadOnlyFieldMap as $tableField)
            unset($row[ $tableField ]);

        $entity = new stdClass();
        $fieldMap = array_flip($this->getReadFieldMap());
        foreach ($row as $field => $value)
        {
            if ($this->isBooleanField($field))
                $value = (bool)$value;

            if (isset($fieldMap[$field]))
                $field = $fieldMap[$field];

            $entity->{$field} = $value;
        }
        return $entity;
    }

    protected function getUniqueEntities (array $entities, array $fields)
    {
        $uniqueEntities = array();
        foreach ($entities as $entity)
        {
            $arr =& $uniqueEntities;
            $fieldCount = count($fields);
            for ($count = 1; $count <= $fieldCount; $count++)
            {
                $field = $fields[$count-1];
                $fieldValue = $entity->$field;
                if ($count == $fieldCount)
                {
                    $arr[$fieldValue] = $entity;
                }
                else
                {
                    $arr[$fieldValue] = array();
                    $arr =& $arr[$fieldValue];
                }
            }
        }

        $result = array();
        array_walk_recursive($uniqueEntities, function ($entity) use (&$result)
        {
            $result[] = $entity;
        });
        return $result;
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
        if (empty($this->hiddenReadOnlyFieldMap))
            return $this->getReadFieldMap();
        else
            return array_merge($this->getReadFieldMap(), $this->hiddenReadOnlyFieldMap);
    }

    private function getReadFieldMap ()
    {
        if (empty($this->readOnlyFieldMap))
            return $this->fieldMap;
        else
            return array_merge($this->fieldMap, $this->readOnlyFieldMap);
    }

    protected final function limitFields (array $fields, array $allowedFields)
    {
        if (empty($fields))
            return $fields;

        // default is to allow all read fields
        if (empty($allowedFields))
            $allowedFields = array_keys($this->getReadFieldMap());

        return array_intersect($fields, $allowedFields);
    }

    /**
     * @param object $entity
     * @param array|null $fields
     * @throws NotSupportedException
     */
    protected function rightJoin ($entity, array $fields = null)
    {
        try
        {
            $joinEntity = $this->getJoinEntity($entity, $fields);
            foreach ($joinEntity as $field => $value)
            {
                // right join means only set the property if it's new (entity doesn't have the property)
                if (!property_exists($entity, $field))
                    $entity->$field = $value;
            }
        }
        catch (ResourceNotFoundException $e)
        {
            foreach ($fields as $field)
            {
                if (!property_exists($entity, $field))
                    $entity->$field = null;
            }
        }
    }

    /**
     * @param object $entity
     * @param array|null $fields
     * @return object join entity
     * @throws NotSupportedException
     */
    protected function getJoinEntity ($entity, array $fields = null)
    {
        throw new NotSupportedException(sprintf('Get all not supported: %s', $this->domain));
    }
}
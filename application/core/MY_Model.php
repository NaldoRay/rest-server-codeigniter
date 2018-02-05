<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once('QueryCondition.php');

/**
 * @author Ray Naldo
 * @property Rest_validation $validation
 */
class MY_Model extends CI_Model
{
    /** for insert/update it's recommended to use $this->getFieldMap() e.g. ['field1' => 'table_field1', 'field2' => 'table_field2'] */
    protected $fieldMap = [];

    /** for view/read-only fields */
    protected $readOnlyFieldMap = [];

    /** prefix from column name with boolean type, for auto-convert */
    protected $booleanPrefixes = [];
    /** prefix from column name with number type (integer, float), for auto-convert */
    protected $numberPrefixes = [];

    /** used as default when no sorts param given when calling get method e.g. ['field1', 'field2'] */
    protected $defaultSorts = [];

    /** will be displayed on validation error result */
    protected $domain = 'API';


    public function __construct ()
    {
        parent::__construct();
        $this->validation->setDomain($this->domain);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param $table
     * @param array $data entity's field => value
     * @param array $filters entity's filter field => filter value
     * @param array|null $allowedFields entity's fields
     * @return bool
     * @throws InvalidFormatException
     * @throws ResourceNotFoundException
     * @throws TransactionException
     */
    protected function createOrUpdateEntity ($db, $table, array $data, array $filters, array $allowedFields = null)
    {
        if ($this->entityExists($db, $table, $filters))
        {
            $entity = $this->updateEntity($db, $table, $data, $filters, $allowedFields);
            return !is_null($entity);
        }
        else
        {
            $entity = $this->createEntity($db, $table, $data, $allowedFields);
            return !is_null($entity);
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $dataArr array of entity data entity's field => value
     * @param array|null $allowedFields
     * @return int number of entities created
     * @throws InvalidFormatException
     */
    protected function createEntities ($db, $table, array $dataArr, array $allowedFields = null)
    {
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
     * @throws InvalidFormatException
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
     * @throws InvalidFormatException
     */
    protected function updateEntities ($db, $table, array $dataArr, $indexField, array $allowedFields = null)
    {
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
     * @throws InvalidFormatException
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
            if (!empty($filters))
                $db->where($filters);

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
     * @param array $conditions QueryCondition[]
     * @throws ResourceNotFoundException
     * @throws TransactionException if delete failed because of database error
     * @internal param QueryCondition[] $filters
     */
    protected function deleteEntityWithConditions ($db, $table, array $conditions)
    {
        $conditions = $this->toTableConditions($conditions);

        foreach ($conditions as $condition)
            $db->where($this->getWhereString($db, $condition));

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
     * @param array $filters table's filter field => filter value
     * @param array $fields table's fields
     * @return array|null
     */
    protected function getRow ($db, $table, array $filters, array $fields = null)
    {
        if (!empty($filters))
            $db->where($filters);

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->limit(1)
            ->get($table);

        return $result->row_array();
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db $db
     * @param string $table
     * @param array $conditions array of QueryCondition
     * @param array $fields entity's fields
     * @return object
     */
    protected function getEntityWithConditions ($db, $table, array $conditions, array $fields = null)
    {
        $tableConditions = $this->toTableConditions($conditions);
        if (!empty($fields))
            $fields = $this->toTableFields($fields);

        foreach ($tableConditions as $condition)
            $db->where($this->getWhereString($db, $condition));

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->limit(1)
            ->get($table);

        $row = $result->row_array();
        if (is_null($row))
            return null;
        else
            return $this->toEntity($row);
    }

    /**
     * @param array $filters entity's filter field => filter value, eg. ['field1' => 'abc']
     * @param array $searches entity's search field => search value
     * @param array $fields entity's fields, eg. ['field1', 'field2']
     * @param array $sorts entity's sort fields, eg. ['field1', '-field2']
     * @param bool $unique
     * @param int $limit
     * @param int $offset
     * @return object[]
     */
    public function getAll (array $filters = null, array $searches = null, array $fields = null, array $sorts = null, $unique = false, $limit = -1, $offset = 0)
    {
        throw new NotSupportedException(sprintf('Get all not supported: %s', $this->domain));
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db $db
     * @param string $table
     * @param array $filters entity's filter field => filter value
     * @param array|null $searches entity's search field => search value
     * @param array $fields entity's fields
     * @param array $sorts entity's sort fields
     * @param bool $unique
     * @param int $limit
     * @param int $offset
     * @return object[]
     */
    protected function getAllEntities ($db, $table, array $filters = null, array $searches = null, array $fields = null, array $sorts = null, $unique = false, $limit = -1, $offset = 0)
    {
        if (!empty($filters))
            $filters = $this->toTableFilters($filters);
        if (!empty($searches))
            $searches = $this->toTableFilters($searches);
        if (!empty($fields))
            $fields = $this->toTableFields($fields);
        if (empty($sorts))
            $sorts = $this->defaultSorts;
        $sorts = $this->toTableSortData($sorts);

        $rows = $this->getAllRows($db, $table, $filters, $searches, $fields, $sorts, $unique, $limit, $offset);
        return $this->toEntities($rows);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db $db
     * @param string $table
     * @param array $filters table's filter field => filter value
     * @param array $searches table's search field => search value
     * @param array $fields table's fields
     * @param array $sorts table's sort fields
     * @param bool $unique
     * @param int $limit
     * @param int $offset
     * @return array
     */
    protected function getAllRows ($db, $table, array $filters = null, array $searches = null, array $fields = null, array $sorts = null, $unique = false, $limit = -1, $offset = 0)
    {
        if (!empty($filters))
            $db->where($filters);

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

        if (!empty($sorts))
        {
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

        if ($unique)
            $db->distinct();

        if ($limit >= 0 || $offset > 0)
        {
            if ($limit < 0)
                $limit = PHP_INT_MAX;
            if ($offset < 0)
                $offset = 0;

            $db->limit($limit, $offset);
        }

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->get($table);

        return $result->result_array();
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db $db
     * @param string $table
     * @param array $conditions array of QueryCondition
     * @param array $fields entity's fields
     * @param array $sorts entity's sort fields
     * @param bool $unique
     * @param int $limit
     * @param int $offset
     * @return object[]
     */
    protected function getAllEntitiesWithConditions ($db, $table, array $conditions, array $fields = null, array $sorts = null, $unique = false, $limit = -1, $offset = 0)
    {
        $tableConditions = $this->toTableConditions($conditions);
        if (!empty($fields))
            $fields = $this->toTableFields($fields);
        if (empty($sorts))
            $sorts = $this->defaultSorts;
        $sorts = $this->toTableSortData($sorts);


        foreach ($tableConditions as $condition)
            $db->where($this->getWhereString($db, $condition));

        if (!empty($sorts))
        {
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

        if ($unique)
            $db->distinct();

        if ($limit >= 0 || $offset > 0)
        {
            if ($limit < 0)
                $limit = PHP_INT_MAX;
            if ($offset < 0)
                $offset = 0;

            $db->limit($limit, $offset);
        }

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->get($table);

        return $this->toEntities($result->result_array());
    }

    private function toTableConditions (array $conditions)
    {
        $tableConditions = array();

        $fieldMap = $this->getFullFieldMap();
        /** @var QueryCondition $condition */
        foreach ($conditions as $condition)
        {
            $field = $condition->field;
            if (isset($fieldMap[$field]))
            {
                $field = $fieldMap[$field];
                $condition->field = $field;

                $value = $condition->value;
                if (empty($value))
                {
                    $tableConditions[] = $condition;
                }
                else
                {
                    try
                    {
                        $value = $this->toTableValue($field, $value);

                        $condition->value = $value;
                        $tableConditions[] = $condition;
                    }
                    catch (InvalidFormatException $e)
                    {}
                }
            }
        }

        return $tableConditions;
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db $db
     * @param QueryCondition $condition
     * @return string
     */
    private function getWhereString ($db, QueryCondition $condition)
    {
        $whereStr = sprintf('%s %s', $condition->field, $condition->operator);
        if (!empty($condition->value))
            $whereStr .= ' ' . $db->escape($condition->value);

        return $whereStr;
    }

    protected function getSelectField (array $tableFields = null)
    {
        $fieldMap = $this->getFullFieldMap();
        if (empty($tableFields))
        {
            if (empty($fieldMap))
                return '*';
            else
                return array_values($fieldMap);
        }
        else
        {
            $tableFields = array_filter($tableFields, function ($value) use ($fieldMap)
            {
                return in_array($value, $fieldMap);
            });
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
     * @param $table
     * @param array $filters table's filter field => filter value
     * @param array $searches table's search field => search value
     * @return bool
     */
    protected function rowExists ($db, $table, array $filters = null, array $searches = null)
    {
        if (!empty($filters))
            $db->where($filters);

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

        $query = $db->select('1')
            ->limit(1)
            ->get($table);

        return ($query->num_rows() > 0);
    }

    /**
     * Filters and maps all fields & values to table format.
     * @param array $data entity's field => value
     * @param array $allowedFields entity's fields
     * @return array
     * @throws InvalidFormatException
     */
    protected function toWriteTableData (array $data, array $allowedFields = null)
    {
        if (empty($data))
            return array();

        $fieldMap = $this->getWriteFieldMap();
        // default is to allow all fields
        if (empty($allowedFields))
            $allowedFields = array_keys($fieldMap);

        $tableData = array();
        foreach ($data as $field => $value)
        {
            if (in_array($field, $allowedFields))
            {
                if (isset($fieldMap[$field]))
                    $field = $fieldMap[$field];

                if ($this->isBooleanField($field))
                {
                    $value = $this->tryParseBoolean($value);
                    $value = ($value ? '1' : '0');
                }
                else if ($this->isNumberField($field))
                {
                    $value = $this->tryParseNumber($value);
                }

                $tableData[$field] = $value;
            }
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

        $fieldMap = $this->getFullFieldMap();
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
                catch (InvalidFormatException $e)
                {}
            }
        }

        return $filterData;
    }

    /**
     * @param string $field table's field
     * @param mixed $value
     * @return bool|string
     * @throws InvalidFormatException
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
    protected function toTableSortData (array $sorts)
    {
        if (empty($sorts))
            return array();

        $sortData = array();
        $fieldMap = $this->getFullFieldMap();
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
        $fieldMap = $this->getFullFieldMap();
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
        unset(
            $row['RNUM']
        );

        $entity = new stdClass();
        $fieldMap = array_flip($this->getFullFieldMap());
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
     * @throws InvalidFormatException if value type is not a boolean or not one of boolean strings: 'true', 'false', '0', '1')
     */
    protected function tryParseBoolean ($value)
    {
        if ($value === 'true' || $value === 'false' || $value === '0' || $value === '1')
            $value = ($value === 'true' || $value === '1');

        if (is_bool($value))
            return $value;
        else
            throw new InvalidFormatException(sprintf('%s is not boolean or boolean string', $value), $this->domain);
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
     * @throws InvalidFormatException if value type is not a number or numeric string
     */
    protected function tryParseNumber ($value)
    {
        if (is_numeric($value))
            return $value + 0;
        else
            throw new InvalidFormatException(sprintf('%s is not a number or numeric string', $value), $this->domain);
    }

    /**
     * @return array default to $this->fieldMap
     */
    protected function getWriteFieldMap ()
    {
        return $this->fieldMap;
    }

    private function getFullFieldMap ()
    {
        if (empty($this->readOnlyFieldMap))
            return $this->fieldMap;
        else
            return array_merge($this->fieldMap, $this->readOnlyFieldMap);
    }
}
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

    /** fields that will be hidden on entity */
    protected $upsertOnlyFieldMap = [];

    /** prefix from column name with boolean type (for auto-convert) */
    protected $booleanPrefixes = [];

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
     * @param array $filters
     * @param array $data
     * @param array $allowedFields
     * @return bool
     */
    protected function insertOrUpdateEntity ($db, $table, array $filters, array $data, array $allowedFields = null)
    {
        if ($this->rowExists($db, $table, $filters))
        {
            return $this->updateEntity($db, $table, $filters, $data, $allowedFields);
        }
        else
        {
            $entity = $this->insertEntity($db, $table, $data, $allowedFields);
            return !is_null($entity);
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $dataArr array of entity data
     * @param array|null $allowedFields
     * @return array
     */
    protected function insertEntities ($db, $table, array $dataArr, array $allowedFields = null)
    {
        foreach ($dataArr as $idx => $data)
            $dataArr[ $idx ] = $this->filterToTableData($data, $allowedFields);

        $count = $db->insert_batch($table, $dataArr);
        if ($count === count($dataArr))
            return $this->toEntities($dataArr);
        else
            return array();
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $data
     * @param array|null $allowedFields
     * @return object|null
     */
    protected function insertEntity ($db, $table, array $data, array $allowedFields = null)
    {
        $data = $this->filterToTableData($data, $allowedFields);
        $success = $this->insertRow($db, $table, $data);
        if ($success)
            return $this->toEntity($data);
        else
            return null;
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $data
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
     * @param array $filters
     * @param array $data
     * @param array|null $allowedFields
     * @return bool
     */
    protected function updateEntity ($db, $table, array $filters, array $data, array $allowedFields = null)
    {
        $filters = $this->toTableFilters($filters);
        $data = $this->filterToTableData($data, $allowedFields);

        return $this->updateRow($db, $table, $filters, $data);
    }

    /**
     * @param CI_DB_query_builder|CI_DB_driver $db
     * @param string $table
     * @param array $filters
     * @param array $data
     * @return bool
     */
    protected function updateRow ($db, $table, array $filters, array $data)
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
     * @param array $filters [entity field => value], e.g. [id => 1]
     * @param array $fields
     * @return object
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
     * @param array $filters [table field => value]
     * @param array $fields
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
     * @param array $fields
     * @return object
     */
    protected function getEntityWithCondition ($db, $table, array $conditions, array $fields = null)
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
     * @param array $filters ['field1' => 'abc']
     * @param array $searches
     * @param array $fields ['field1', 'field2']
     * @param array $sorts ['field1', '-field2']
     * @param bool $unique
     * @return object[]
     */
    public function getAll (array $filters = null, array $searches = null, array $fields = null, array $sorts = null, $unique = false)
    {
        throw new NotSupportedException(sprintf('Get all not supported: %s', $this->domain));
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db $db
     * @param string $table
     * @param array $filters
     * @param array|null $searches
     * @param array $fields
     * @param array $sorts
     * @param bool $unique
     * @return object[]
     */
    protected function getAllEntities ($db, $table, array $filters = null, array $searches = null, array $fields = null, array $sorts = null, $unique = false)
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

        $rows = $this->getAllRows($db, $table, $filters, $searches, $fields, $sorts, $unique);
        return $this->toEntities($rows);
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db $db
     * @param string $table
     * @param array $filters
     * @param array $searches
     * @param array $fields
     * @param array $sorts
     * @param bool $unique
     * @return object[]
     */
    protected function getAllRows ($db, $table, array $filters = null, array $searches = null, array $fields = null, array $sorts = null, $unique = false)
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

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->get($table);

        return $result->result_array();
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db $db
     * @param string $table
     * @param array $conditions array of QueryCondition
     * @param array $fields
     * @param array $sorts
     * @param bool $unique
     * @return object[]
     */
    protected function getAllEntitiesWithConditions ($db, $table, array $conditions, array $fields = null, array $sorts = null, $unique = false)
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

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->get($table);

        return $this->toEntities($result->result_array());
    }

    private function toTableConditions (array $conditions)
    {
        $tableConditions = array();

        /** @var QueryCondition $condition */
        foreach ($conditions as $condition)
        {
            $field = $condition->field;
            if (isset($this->fieldMap[$field]))
            {
                $condition->field = $this->fieldMap[$field];
                $tableConditions[] = $condition;
            }
        }
        return $tableConditions;
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db $db
     * @param QueryCondition $condition
     * @return string
     */
    private function getWhereString ($db, QueryCondition $condition)
    {
        return sprintf('%s %s %s', $condition->field, $condition->operator, $db->escape($condition->value));
    }

    protected function getSelectField (array $tableFields = null)
    {
        if (empty($tableFields))
        {
            if (empty($this->fieldMap))
                return '*';
            else
                return array_values($this->fieldMap);
        }
        else
        {
            $tableFields = array_filter($tableFields, function ($value)
            {
                return in_array($value, $this->fieldMap);
            });
            return implode(',', $tableFields);
        }
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db
     * @param $table
     * @param array $filters
     * @param array $searches
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
     * @param CI_DB_query_builder|CI_DB $db
     * @param $table
     * @param array $filters
     * @param array $searches
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
     * @param array $data
     * @param array $allowedFields
     * @return array
     */
    protected function filterToTableData (array $data, array $allowedFields = null)
    {
        if (empty($data))
            return array();

        $tableData = array();

        // default is to allow all fields
        if (empty($allowedFields))
            $allowedFields = array_keys($this->getFullFieldMap());
        else
            $allowedFields = array_merge($allowedFields, array_keys($this->upsertOnlyFieldMap));

        $fieldMap = $this->getFullFieldMap();
        foreach ($data as $field => $value)
        {
            if (in_array($field, $allowedFields))
            {
                if (isset($fieldMap[$field]))
                    $field = $fieldMap[$field];

                if ($this->isBooleanField($field))
                    $value = ($value ? '1' : '0');

                $tableData[$field] = $value;
            }
        }

        return $tableData;
    }

    protected function toTableFilters (array $filters)
    {
        if (empty($filters))
            return array();

        $filterData = array();
        foreach ($filters as $field => $value)
        {
            if (isset($this->fieldMap[$field]))
            {
                $field = $this->fieldMap[$field];
                if ($this->isBooleanField($field))
                {
                    // set field only if it has valid value
                    if ($value === 'true' || $value === 'false' || $value === '0' || $value === '1')
                        $value = ($value === 'true' || $value === '1');

                    if (is_bool($value))
                        $filterData[$field] = ($value ? '1' : '0');
                }
                else
                {
                    $filterData[$field] = $value;
                }
            }
        }
        return $filterData;
    }

    /**
     * Maps all sort fields to table sort fields
     * @param array $sorts
     * @return array
     */
    protected function toTableSortData (array $sorts)
    {
        if (empty($sorts))
            return array();

        $sortData = array();
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

            if (isset($this->fieldMap[$field]))
            {
                $sortData[] = sprintf('%s %s', $this->fieldMap[$field], $order);
            }
        }
        return $sortData;
    }

    /**
     * Maps all fields to table fields.
     * @param array $fields
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
     * @param object[] $rows table rows
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
        $fieldMap = array_flip($this->fieldMap);
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

    private function isBooleanField ($field)
    {
        foreach ($this->booleanPrefixes as $booleanPrefix)
        {
            if (strpos($field, $booleanPrefix) === 0)
                return true;
        }
        return false;
    }

    private function getFullFieldMap ()
    {
        return array_merge($this->fieldMap, $this->upsertOnlyFieldMap);
    }
}
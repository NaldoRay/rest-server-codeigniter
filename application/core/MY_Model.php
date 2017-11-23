<?php

/**
 * @author Ray Naldo
 * @property Rest_validation $validation
 */
abstract class MY_Model extends CI_Model
{
    /** for insert/update it's recommended to use $this->getFieldMap() e.g. ['field1' => 'table_field1', 'field2' => 'table_field2'] */
    protected $fieldMap = [];

    /** fields that will be hidden on entity */
    protected $upsertOnlyFields = [];

    /** prefix from column name with boolean type (for auto-convert) */
    protected $booleanPrefixes = ['is_', 'has_'];

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
     * @param CI_DB_query_builder|CI_DB $db
     * @param string $table
     * @param array $filters not table's [field => value] but this model (unmapped) [field => value], e.g. [isActive => true]
     * @param array $fields
     * @return object
     */
    protected function getRow ($db, $table, array $filters = array(), array $fields = array())
    {
        $filters = $this->filterToTableData($filters);
        $fields = $this->toTableFields($fields);

        return $this->getEntity($db, $table, $filters, $fields);
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db
     * @param string $table
     * @param array $whereArr table's [field => value]
     * @param array $fields
     * @return object
     */
    protected function getEntity ($db, $table, array $whereArr = array(), array $fields = array())
    {
        if (!empty($whereArr))
            $db->where($whereArr);

        $select = $this->getSelectField($fields);
        $result = $db->select($select)
            ->get($table);

        return $this->toEntity($result->row_array());
    }

    /**
     * @param array $fields ['field1', 'field2']
     * @param array $filters ['field1' => 'abc']
     * @param array $sorts ['field1', '-field2']
     * @param bool $unique
     * @return object[]
     */
    public abstract function getAll (array $fields = array(), array $filters = array(), array $sorts = array(), $unique = false);

    /**
     * @param CI_DB_query_builder|CI_DB $db $db
     * @param string $table
     * @param array|null $fields
     * @param array $filters
     * @param array $sorts
     * @param bool $unique
     * @return object[]
     */
    protected function getAllRows ($db, $table, array $fields = array(), array $filters = array(), array $sorts = array(), $unique = false)
    {
        $fields = $this->toTableFields($fields);
        $filters = $this->filterToTableData($filters); // allow all fields in $fieldMap
        if (empty($sorts))
            $sorts = $this->defaultSorts;
        $sorts = $this->toTableSortData($sorts);

        return $this->getAllEntities($db, $table, $fields, $filters, $sorts, $unique);
    }

    /**
     * @param CI_DB_query_builder|CI_DB $db $db
     * @param string $table
     * @param array|null $fields
     * @param array $filters
     * @param array $sorts
     * @param bool $unique
     * @return object[]
     */
    protected function getAllEntities ($db, $table, array $fields = array(), array $filters = array(), array $sorts = array(), $unique = false)
    {
        foreach ($filters as $field => $filter)
        {
            $filter = $db->escape($filter);
            $db->where(sprintf("LOWER(%s) LIKE ('%%'||LOWER(%s)||'%%')", $field, $filter), null, false);
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

        return $this->toEntities($result->result_array());
    }

    protected function getSelectField (array $tableFields)
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
     * @param $whereArr
     * @return bool
     */
    protected function rowExists ($db, $table, $whereArr)
    {
        $query = $db->select('1')
            ->where($whereArr)
            ->get($table);

        return ($query->num_rows() > 0);
    }

    /**
     * Filters and maps all fields & values to table format.
     * @param array $data
     * @param array $allowedFields
     * @return array
     */
    protected function filterToTableData (array $data, array $allowedFields = array())
    {
        if (empty($data))
            return array();

        $tableData = array();

        // default is to allow all fields
        if (empty($allowedFields))
            $allowedFields = array_keys($this->getFullFieldMap());
        else
            $allowedFields = array_merge($allowedFields, array_keys($this->upsertOnlyFields));

        $fieldMap = $this->getFullFieldMap();
        foreach ($data as $field => $value)
        {
            if (in_array($field, $allowedFields))
            {
                if (is_bool($value))
                    $value = ($value ? '1' : '0');

                if (isset($fieldMap[$field]))
                    $field = $fieldMap[$field];

                $tableData[$field] = $value;
            }
        }

        return $tableData;
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
    protected function toTableFields (array $fields = array())
    {
        if (empty($fields))
            return array();

        $tableFields = array();
        $fieldMap = $this->getFullFieldMap();
        foreach ($fields as $field)
        {
            if (isset($fieldMap[$field]))
                $field = $fieldMap[$field];

            $tableFields[] = $field;
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
            return $this->toEntity($row);
        }, $rows);
    }

    /**
     * @param array $row table row
     * @return object
     */
    protected function toEntity (array $row = null)
    {
        if (is_null($row))
            return null;

        $entity = new stdClass();
        $fieldMap = array_flip($this->fieldMap);
        foreach ($row as $field => $value)
        {
            foreach ($this->booleanPrefixes as $booleanPrefix)
            {
                if (strpos($field, $booleanPrefix) === 0)
                {
                    $value = (bool)$value;
                    break;
                }
            }

            if (isset($fieldMap[$field]))
                $field = $fieldMap[$field];

            $entity->{$field} = $value;
        }
        return $entity;
    }

    private function getFullFieldMap ()
    {
        return array_merge($this->fieldMap, $this->upsertOnlyFields);
    }
}
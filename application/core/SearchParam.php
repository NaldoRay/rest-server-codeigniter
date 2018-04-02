<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class SearchParam
{
    /** @var FieldsFilter */
    private $fieldsFilter = null;
    /** @var QueryCondition */
    private $condition = null;
    /** @var array */
    private $sorts = null;
    private $limit = -1;
    private $offset = 0;


    public function getFields ()
    {
        return $this->fieldsFilter->getFields();
    }

    public function fieldExists ($field)
    {
        return $this->fieldsFilter->fieldExists($field);
    }

    /**
     * @param string $field
     * @return FieldsFilter
     */
    public function getSubSelect ($field)
    {
        if (is_null($this->fieldsFilter))
            return null;
        else
            return $this->fieldsFilter->getFieldsFilter($field);
    }

    /**
     * @param string $fieldsParam e.g. 'id,date,customer/name,items(name,price,quantity)'
     * @return $this
     */
    public function selectFromString ($fieldsParam)
    {
        $this->fieldsFilter = FieldsFilter::createFromString($fieldsParam);
        return $this;
    }

    /**
     * @param array $fields e.g. ['id', 'date', 'customer/name', 'items(name,price,quantity)']
     * @return $this
     */
    public function selectFromArray (array $fields)
    {
        $this->fieldsFilter = FieldsFilter::create($fields);
        return $this;
    }

    public function select (FieldsFilter $fieldsFilter)
    {
        $this->fieldsFilter = $fieldsFilter;
        return $this;
    }

    /**
     * @return QueryCondition
     */
    public function getCondition ()
    {
        return $this->condition;
    }

    /**
     * @param QueryCondition $condition
     * @return $this
     */
    public function withCondition (QueryCondition $condition)
    {
        $this->condition = $condition;
        return $this;
    }

    /**
     * @return array
     */
    public function getSorts ()
    {
        return $this->sorts;
    }

    /**
     * @param array $sorts e.g. [name, -date]
     * @return $this
     */
    public function sort (array $sorts)
    {
        $this->sorts = $sorts;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit ()
    {
        return $this->limit;
    }

    /**
     * @return int
     */
    public function getOffset ()
    {
        return $this->offset;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit ($limit, $offset = 0)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }
}
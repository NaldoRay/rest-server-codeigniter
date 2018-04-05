<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class QueryParam
{
    /** @var FieldsFilter */
    private $fieldsFilter = null;
    /** @var array */
    private $filters = array();
    /** @var array */
    private $searches = array();
    /** @var QueryCondition */
    private $condition = null;
    /** @var array */
    private $sorts = null;
    private $limit = -1;
    private $offset = 0;
    private $distinct = false;


    /**
     * @param QueryParam|null $param if supplied, this QueryParam will have same properties value
     */
    public function __construct (QueryParam $param = null)
    {
        if (!is_null($param))
        {
            $this->fieldsFilter = $param->fieldsFilter;
            $this->filters = $param->filters;
            $this->searches = $param->searches;
            $this->condition = $param->condition;
            $this->sorts = $param->sorts;
            $this->limit = $param->limit;
            $this->offset = $param->offset;
            $this->distinct = $param->distinct;
        }
    }


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
     * @param array $filters e.g. ['id' => 12, 'active' => true]
     * @return $this
     */
    public function filter (array $filters)
    {
        $this->filters = $filters;
        return $this;
    }

    /**
     * @param array $searches e.g. ['name' => 'contains']
     * @return $this
     */
    public function search (array $searches)
    {
        $this->searches = $searches;
        return $this;
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
     * @return QueryCondition
     */
    public function getCondition ()
    {
        $subConditions = array();
        if (!is_null($this->condition))
            $subConditions[] = $this->condition;

        $filtersCondition = $this->getFiltersCondition();
        if (!is_null($filtersCondition))
            $subConditions[] = $filtersCondition;

        $searchesCondition = $this->getSearchesCondition();
        if (!is_null($searchesCondition))
            $subConditions[] = $searchesCondition;

        if (empty($subConditions))
            return null;
        else
            return LogicalCondition::logicalAnd($subConditions);
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

    public function isDistinct ()
    {
        return $this->distinct;
    }

    public function distinct ()
    {
        $this->distinct = true;
    }

    private function getFiltersCondition ()
    {
        if (empty($this->filters))
            return null;

        $filterConditions = array();
        foreach ($this->filters as $field => $value)
            $filterConditions[] = new EqualsCondition($field, $value);

        return LogicalCondition::logicalAnd($filterConditions);
    }

    private function getSearchesCondition ()
    {
        if (empty($this->searches))
            return null;

        $filterConditions = array();
        foreach ($this->searches as $field => $value)
            $filterConditions[] = new ContainsCondition($field, $value, true);

        return LogicalCondition::logicalOr($filterConditions);
    }
}
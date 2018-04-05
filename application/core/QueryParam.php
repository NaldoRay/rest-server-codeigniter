<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class QueryParam
{
    /** @var array */
    private $filters;
    /** @var QueryCondition[] */
    private $searches;
    /** @var array */
    private $sorts;
    private $limit;
    private $offset;


    public static function createFilter (array $filters)
    {
        return self::create()->filter($filters);
    }

    public static function createSearch (QueryCondition $condition)
    {
        return self::create()->search($condition);
    }

    public static function create ()
    {
        return new QueryParam();
    }

    private function __construct ()
    {
        $this->resetCondition();
        $this->resetSort();
        $this->resetLimit();
    }

    /**
     * @return QueryCondition|null
     */
    public function getCondition ()
    {
        $conditions = array();

        $filtersCondition = $this->getFiltersCondition();
        if (!is_null($filtersCondition))
            $conditions[] = $filtersCondition;

        $searchesCondition = $this->getSearchesCondition();
        if (!is_null($searchesCondition))
            $conditions[] = $searchesCondition;

        if (empty($conditions))
            return null;
        else
            return LogicalCondition::logicalAnd($conditions);
    }

    private function getFiltersCondition ()
    {
        if (empty($this->filters))
            return null;

        $conditions = array();
        foreach ($this->filters as $field => $value)
            $conditions[] = new EqualsCondition($field, $value);

        return LogicalCondition::logicalAnd($conditions);
    }

    private function getSearchesCondition ()
    {
        if (empty($this->searches))
            return null;

        return LogicalCondition::logicalAnd($this->searches);
    }

    public function resetCondition ()
    {
        $this->filters = array();
        $this->searches = array();
        return $this;
    }

    /**
     * Subsequent calls will add the filters to the previous.
     * @param array $filters e.g. ['id' => 12, 'active' => true]
     * @return $this
     */
    public function filter (array $filters)
    {
        $this->filters = array_merge($this->filters, $filters);
        return $this;
    }

    /**
     * Subsequent calls will add the condition to the previous.
     * @param QueryCondition $condition
     * @return $this
     */
    public function search (QueryCondition $condition)
    {
        $this->searches[] = $condition;
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

    public function resetSort ()
    {
        $this->sorts = array();
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

    public function resetLimit ()
    {
        $this->limit = -1;
        $this->offset = 0;
        return $this;
    }
}
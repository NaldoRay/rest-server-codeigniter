<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class SearchParam extends RequestParam
{
    /** @var QueryCondition */
    private $condition;


    public static function createSearch (QueryCondition $condition)
    {
        return self::create()->search($condition);
    }

    protected function __construct ()
    {
        parent::__construct();
        $this->resetSearch();
    }

    /**
     * @return QueryCondition
     */
    public function getSearchCondition ()
    {
        return $this->condition;
    }

    /**
     * @param QueryCondition $condition
     * @return $this
     */
    public function search (QueryCondition $condition)
    {
        $this->condition = $condition;
        return $this;
    }

    public function resetSearch ()
    {
        $this->condition = null;
        return $this;
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class GetParam extends RequestParam
{
    /** @var array */
    private $filters;


    public static function createFilter (array $filters)
    {
        return self::create()->filter($filters);
    }

    protected function __construct ()
    {
        parent::__construct();
        $this->resetFilters();
    }

    /**
     * @return array
     */
    public function getFilters ()
    {
        return $this->filters;
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

    public function resetFilters ()
    {
        $this->filters = array();
        return $this;
    }
}
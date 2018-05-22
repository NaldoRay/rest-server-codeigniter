<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class RequestParam
{
    /** @var array */
    private $fields;
    /** @var array */
    private $expands;
    /** @var array */
    private $sorts;
    private $limit;
    private $offset;


    public static function create ()
    {
        return new static();
    }

    protected function __construct ()
    {
        $this->resetFields();
        $this->resetExpands();
        $this->resetSort();
        $this->resetLimit();
    }

    /**
     * @return array
     */
    public function getFields ()
    {
        return $this->fields;
    }

    public function select (array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function resetFields ()
    {
        $this->fields = array();
        return $this;
    }

    /**
     * @return array
     */
    public function getExpands ()
    {
        return $this->expands;
    }

    /**
     * @param array $expands
     * @return $this
     */
    public function expand (array $expands)
    {
        $this->expands = $expands;
        return $this;
    }

    public function resetExpands ()
    {
        $this->expands = array();
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
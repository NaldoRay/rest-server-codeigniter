<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class MY_Condition
 * @author Ray Naldo
 */
class QueryCondition
{
    public $field;
    public $operator;
    public $value;

    /**
     * MY_Condition constructor.
     * @param string $field
     * @param string $operator one of {=, !=, >, >=, <, <=}
     * @param mixed $value
     */
    public function __construct ($field, $operator, $value = null)
    {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
    }
}
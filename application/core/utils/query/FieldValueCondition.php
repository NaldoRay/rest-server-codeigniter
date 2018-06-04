<?php

require_once('QueryCondition.php');

/**
 * @author Ray Naldo
 */
abstract class FieldValueCondition implements QueryCondition
{
    private $field;
    private $operator;
    private $value;


    /**
     * QuerySimpleCondition constructor.
     * @param string $field
     * @param string $operator
     * @param mixed $value
     */
    public function __construct ($field, $operator, $value)
    {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getField ()
    {
        return $this->field;
    }

    /**
     * @return mixed
     */
    public function getValue ()
    {
        return $this->value;
    }

    /**
     * @param string $field
     * @param mixed $value
     */
    public function setFieldValue ($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    public function jsonSerialize ()
    {
        return get_object_vars($this);
    }
}
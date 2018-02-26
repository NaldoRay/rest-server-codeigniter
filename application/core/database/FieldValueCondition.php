<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('QueryCondition.php');

/**
 * @author Ray Naldo
 */
abstract class FieldValueCondition implements QueryCondition
{
    private $field;
    private $value;


    /**
     * QuerySimpleCondition constructor.
     * @param $field
     * @param $value
     */
    public function __construct ($field, $value)
    {
        $this->field = $field;
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
     * @return FieldValueCondition
     */
    public function setFieldValue ($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
    }


}
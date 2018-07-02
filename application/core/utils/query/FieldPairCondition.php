<?php

require_once('QueryCondition.php');

/**
 * @author Ray Naldo
 */
abstract class FieldPairCondition implements QueryCondition
{
    private $leftField;
    private $operator;
    private $rightField;


    /**
     * QuerySimpleCondition constructor.
     * @param string $leftField
     * @param string $operator
     * @param string $rightField
     */
    protected function __construct ($leftField, $operator, $rightField)
    {
        $this->leftField = $leftField;
        $this->operator = $operator;
        $this->rightField = $rightField;
    }

    public function getConditionString ()
    {
        return sprintf('%s %s %s', $this->leftField, $this->operator, $this->rightField);
    }

    /**
     * @return mixed
     */
    public function getLeftField ()
    {
        return $this->leftField;
    }

    /**
     * @return string
     */
    public function getOperator ()
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getRightField ()
    {
        return $this->rightField;
    }

    /**
     * @param string $leftField
     * @param mixed $rightField
     */
    public function setFieldPair ($leftField, $rightField)
    {
        $this->leftField = $leftField;
        $this->rightField = $rightField;
    }

    public function jsonSerialize ()
    {
        return get_object_vars($this);
    }
}
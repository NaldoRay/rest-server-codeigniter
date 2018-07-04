<?php

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class NotCondition implements QueryCondition
{
    private $condition;

    public function __construct (QueryCondition $condition)
    {
        $this->condition = $condition;
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
     */
    public function setCondition ($condition)
    {
        $this->condition = $condition;
    }

    public function getConditionString ()
    {
        return sprintf('NOT(%s)', $this->condition->getConditionString());
    }

    public function jsonSerialize ()
    {
        return get_object_vars($this);
    }
}
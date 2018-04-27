<?php

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class LessThanCondition extends FieldValueCondition
{
    public function __construct ($field, $value)
    {
        parent::__construct($field, '<', $value);
    }

    public function getConditionString ()
    {
        return sprintf('%s < %s', $this->getField(), $this->getValue());
    }
}
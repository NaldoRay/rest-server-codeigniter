<?php

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class NotEqualsCondition extends FieldValueCondition
{
    public function getConditionString ()
    {
        $field = $this->getField();
        $value = $this->getValue();

        if (is_array($value))
            return sprintf('%s NOT IN (%s)', $field, implode(',', $value));
        else if (is_null($value))
            return sprintf('%s IS NOT NULL', $field);
        else
            return sprintf('%s != %s', $field, $value);
    }
}
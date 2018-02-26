<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class EqualsCondition extends FieldValueCondition
{
    public function getConditionString ()
    {
        $field = $this->getField();
        $value = $this->getValue();

        if (is_array($value))
            return sprintf('%s IN (%s)', $field, $value);
        else if (is_null($value))
            return sprintf('%s IS NULL', $field);
        else
            return sprintf('%s = %s', $field, $value);
    }
}
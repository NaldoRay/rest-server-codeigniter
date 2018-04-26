<?php

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class ContainsCondition extends FieldValueCondition
{
    private $ignoreCase;

    public function __construct ($field, $value, $ignoreCase = false)
    {
        parent::__construct($field, ($ignoreCase ? '~' : '~~'), $value);
        $this->ignoreCase = $ignoreCase;
    }

    public function getConditionString ()
    {
        if ($this->ignoreCase)
            return sprintf("LOWER(%s) LIKE ('%%'||LOWER(%s)||'%%')", $this->getField(), $this->getValue());
        else
            return sprintf("%s LIKE '%%%s%%'", $this->getField(), $this->getValue());
    }
}
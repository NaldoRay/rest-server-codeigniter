<?php

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class NotContainsCondition extends FieldValueCondition
{
    private $ignoreCase;

    public function __construct ($field, $value, $ignoreCase = false)
    {
        parent::__construct($field, $value);
        $this->ignoreCase = $ignoreCase;
    }

    public function getConditionString ()
    {
        if ($this->ignoreCase)
            return sprintf("LOWER(%s) NOT LIKE ('%%'||LOWER(%s)||'%%')", $this->getField(), $this->getValue());
        else
            return sprintf("%s NOT LIKE '%%%s%%'", $this->getField(), $this->getValue());
    }
}
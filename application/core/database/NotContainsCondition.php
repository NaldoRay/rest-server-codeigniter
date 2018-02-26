<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class NotContainsCondition extends FieldValueCondition
{
    public function getConditionString ()
    {
        return sprintf("%s NOT LIKE '%%%s%%'", $this->getField(), $this->getValue());
    }
}
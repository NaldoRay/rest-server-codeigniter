<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class ContainsCondition extends FieldValueCondition
{
    public function getConditionString ()
    {
        return sprintf("%s LIKE '%%%s%%'", $this->getField(), $this->getValue());
    }
}
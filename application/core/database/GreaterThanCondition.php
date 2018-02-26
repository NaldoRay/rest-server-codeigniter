<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class GreaterThanCondition extends FieldValueCondition
{
    public function getConditionString ()
    {
        return sprintf('%s > %s', $this->getField(), $this->getValue());
    }
}
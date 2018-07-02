<?php

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class LessEqualsFieldCondition extends FieldPairCondition
{
    public function __construct ($leftField, $rightField)
    {
        parent::__construct($leftField, '<=', $rightField);
    }
}
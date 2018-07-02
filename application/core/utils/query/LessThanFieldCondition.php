<?php

require_once('FieldValueCondition.php');

/**
 * @author Ray Naldo
 */
class LessThanFieldCondition extends FieldPairCondition
{
    public function __construct ($leftField, $rightField)
    {
        parent::__construct($leftField, '<', $rightField);
    }
}
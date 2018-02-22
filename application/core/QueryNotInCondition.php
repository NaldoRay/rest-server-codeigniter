<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('QueryCondition.php');

/**
 * Class MY_Condition
 * @author Ray Naldo
 */
class NotInCondition extends QueryCondition
{
    /**
     * MY_Condition constructor.
     * @param string $field
     * @param array $values
     */
    public function __construct ($field, array $values)
    {
        parent::__construct($field, sprintf("NOT IN ('%s')", implode("','", $values)));
    }
}
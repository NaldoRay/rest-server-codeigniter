<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
interface QueryCondition
{
    /**
     * @return string
     */
    public function getConditionString ();
}
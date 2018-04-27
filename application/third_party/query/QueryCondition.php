<?php

/**
 * @author Ray Naldo
 */
interface QueryCondition extends JsonSerializable
{
    /**
     * @return string
     */
    public function getConditionString ();
}
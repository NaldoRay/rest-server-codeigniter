<?php

require_once('QueryCondition.php');

/**
 * @author Ray Naldo
 */
class RawCondition implements QueryCondition
{
    private $rawCondition;


    /**
     * QuerySimpleCondition constructor.
     * @param string $rawCondition
     */
    public function __construct ($rawCondition)
    {
        $this->rawCondition = $rawCondition;
    }

    public function getConditionString ()
    {
        return $this->rawCondition;
    }

    public function jsonSerialize ()
    {
        return get_object_vars($this);
    }
}
<?php

require_once('QueryCondition.php');

/**
 * @author Ray Naldo
 */
class LogicalCondition implements QueryCondition
{
    private $operator;
    /** @var QueryCondition[] */
    private $conditions;


    public static function logicalAnd (array $conditions)
    {
        return new LogicalCondition('AND', $conditions);
    }

    public static function logicalOr (array $conditions)
    {
        return new LogicalCondition('OR', $conditions);
    }

    /**
     * MY_Condition constructor.
     * @param string $operator
     * @param QueryCondition[] $conditions
     */
    private function __construct ($operator, array $conditions)
    {
        $this->operator = $operator;
        $this->conditions = $conditions;
    }

    /**
     * @return QueryCondition[]
     */
    public function getConditions ()
    {
        return $this->conditions;
    }

    public function getConditionString ()
    {
        if (empty($this->conditions))
            return '';

        $conditionStrings = array();
        foreach ($this->conditions as $condition)
            $conditionStrings[] = $condition->getConditionString();

        $conjunction = sprintf(' %s ', $this->operator);
        return sprintf('(%s)', implode($conjunction, $conditionStrings));
    }

    public function setConditions (array $conditions)
    {
        $this->conditions = $conditions;
    }
}
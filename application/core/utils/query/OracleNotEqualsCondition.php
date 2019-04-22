<?php

require_once('FieldValueCondition.php');

/**
 * Fixes ORA-01795: maximum number of expressions in a list is 1000.
 * @author Ray Naldo
 */
class OracleNotEqualsCondition extends NotEqualsCondition
{
    public function getConditionString ()
    {
        $value = $this->getValue();
        if (is_array($value))
        {
            $count = count($value);
            if ($count > 1000)
            {
                /*
                 * Because Oracle implemented normal NOT IN (...) with a limit of 1,000 elements,
                 * As a workaround, there's multi-value comparison. More than or equals to 100,000 elements will not handled as currently there's no workaround.
                 * Reference: https://stackoverflow.com/a/17019130
                 * But CodeIgniter is not able to parse the regex if the value is too much.
                 * After experimenting, although slow, the most robust way is by using multiple NOT IN (...) joined with AND.
                 */
                $field = $this->getField();
                $whereNotInArr = array();
                for ($i = 0; $i < $count; $i += 1000)
                {
                    $whereNotInArr[] = sprintf("%s NOT IN (%s)", $field, implode(',', array_slice($value, $i, 1000)));
                }

                return sprintf("(%s)", implode(' AND ', $whereNotInArr));
            }
        }

        return parent::getConditionString();
    }
}
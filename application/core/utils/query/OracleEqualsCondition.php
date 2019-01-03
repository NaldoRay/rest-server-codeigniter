<?php

require_once('FieldValueCondition.php');

/**
 * Fixes ORA-01795: maximum number of expressions in a list is 1000.
 * @author Ray Naldo
 */
class OracleEqualsCondition extends EqualsCondition
{
    public function getConditionString ()
    {
        $value = $this->getValue();
        if (is_array($value))
        {
            if (count($value) > 1000)
            {
                /*
                 * Because Oracle implemented normal IN list with a limit of 1,000 elements,
                 * then as a workaround, use multi-value comparison.
                 * More than or equals to 100,000 elements will not handled as currently there's no workaround.
                 * https://stackoverflow.com/a/17019130
                 */

                $multiValues = array();
                foreach ($value as $val)
                    $multiValues[] = sprintf("('magic',%s)", $val);

                return sprintf("('magic',%s) IN (%s)", $this->getField(), implode(',', $multiValues));
            }
        }

        return parent::getConditionString();
    }
}
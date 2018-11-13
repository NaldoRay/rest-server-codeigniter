<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * @author Ray Naldo
 */
class ValueValidatorCI extends ValueValidator
{
    private $CI;


    public function __construct ($value, $label = 'Value')
    {
        parent::__construct($value, $label);
        $this->CI = get_instance();
    }

    public function notEmpty ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_not_empty');

        return parent::notEmpty($errorMessage);
    }

    public function lengthMin ($min, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_length_min');

        return parent::lengthMin($min, $errorMessage);
    }

    public function lengthMax ($max, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_length_max');

        return parent::lengthMax($max, $errorMessage);
    }

    public function lengthBetween ($min, $max, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_length_between');

        return parent::lengthBetween($min, $max, $errorMessage);
    }

    public function lengthEquals ($length, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_length_equals');

        return parent::lengthEquals($length, $errorMessage);
    }

    public function validEmail ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_email');

        return parent::validEmail($errorMessage);
    }

    public function validDate ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_date');

        return parent::validDate($errorMessage);
    }

    public function validDateTime ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_datetime');

        return parent::validDateTime($errorMessage);
    }

    public function onlyBoolean ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_boolean');

        return parent::onlyBoolean($errorMessage);
    }

    public function onlyInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_integer');

        return parent::onlyInteger($errorMessage);
    }

    public function onlyPositiveInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_integer_positive');

        return parent::onlyPositiveInteger($errorMessage);
    }

    public function onlyFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_float');

        return parent::onlyFloat($errorMessage);
    }

    public function onlyPositiveFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_float_positive');

        return parent::onlyPositiveFloat($errorMessage);
    }

    public function onlyString ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_string');

        return parent::onlyString($errorMessage);
    }

    public function onlyDigit ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_digit');

        return parent::onlyDigit($errorMessage);
    }

    public function onlyNumeric ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_numeric');

        return parent::onlyNumeric($errorMessage);
    }

    public function onlyNumericInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_numeric_integer');

        return parent::onlyNumericInteger($errorMessage);
    }

    public function onlyPositiveNumericInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_numeric_integer_positive');

        return parent::onlyPositiveNumericInteger($errorMessage);
    }

    public function onlyNumericFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_numeric_float');

        return parent::onlyNumericFloat($errorMessage);
    }

    public function onlyPositiveNumericFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_numeric_float_positive');

        return parent::onlyPositiveNumericFloat($errorMessage);
    }

    public function onlyArray ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_array');

        return parent::onlyArray($errorMessage);
    }

    public function onlyNumericArray ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_numeric_array');

        return parent::onlyNumericArray($errorMessage);
    }

    public function onlyAssociativeArray ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_associative_array');

        return parent::onlyAssociativeArray($errorMessage);
    }

    public function onlyArrayOfAssociatives ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_array_associatives');

        return parent::onlyArrayOfAssociatives($errorMessage);
    }

    public function onlyArrayOfObjects ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_array_objects');

        return parent::onlyArrayOfObjects($errorMessage);
    }

    public function onlyOneOf (array $values, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_one_of_values');

        return parent::onlyOneOf($values, $errorMessage);
    }

    private function getString ($key)
    {
        $line = $this->CI->lang->line($key);
        if ($line === false)
            return null;
        else
            return $line;
    }
}
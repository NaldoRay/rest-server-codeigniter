<?php

require_once('ValueValidator.php');

/**
 * Validation is optional by default (validations
 * @author Ray Naldo
 */
class ArrayValueValidator extends ValueValidator
{
    private $undefined;
    private $optional = true;

    private $requiredMessage = '';


    /**
     * ArrayValueValidator constructor.
     * @param array $arr
     * @param string $field
     * @param string $label
     */
    public function __construct (array $arr, $field, $label = 'Value')
    {
        parent::__construct(null, $label);
        $this->setArrayValue($arr, $field);
    }

    /**
     * @param array $arr
     * @param $field
     */
    function setArrayValue (array $arr, $field)
    {
        $this->undefined = !array_key_exists($field, $arr);

        $value = ($this->undefined ? null : $arr[$field]);
        $this->setValue($value);
    }

    /**
     * Validation failed if array value is 'undefined' (field not set)
     * @param string $errorMessage custom error message
     * @return $this
     */
    public function required ($errorMessage = null)
    {
        $this->optional = false;

        if (is_null($errorMessage))
            $errorMessage = '{label} is required';
        $this->requiredMessage = $errorMessage;

        return $this;
    }

    public function validate ()
    {
        if ($this->undefined)
        {
            if ($this->optional)
            {
                $this->resetError();
                return true;
            }
            else
            {
                $errorMessage = $this->formatErrorMessage($this->requiredMessage);
                $this->setError($errorMessage);
                return false;
            }
        }

        return parent::validate();
    }
}
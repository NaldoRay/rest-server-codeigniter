<?php

require_once('Validation.php');

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 10:20
 */
class ValueValidator implements Validation
{
    private static $IDX_REQUIRED = 0;
    private static $IDX_ONLY_NUMERIC = 1;
    private static $IDX_VALID_EMAIL = 2;
    private static $IDX_LENGTH_MIN = 3;
    private static $IDX_LENGTH_MAX = 4;
    private static $IDX_LENGTH_BETWEEN = 5;

    private static $IDX_OTHER = 10;

    /** @var mixed */
    private $value;
    /** @var string */
    private $label;

    /** @var array */
    private $validations;
    /** @var array */
    private $errorMessages;
    /** @var string */
    private $error;


    public function __construct ($value, $label = 'Value')
    {
        $this->value = $value;
        $this->label = $label;

        $this->validations = array();
        $this->errorMessages = array();
        $this->error = null;
    }

    /**
     * @return $this
     */
    public function required ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s is required', $this->label);

        $this->setValidation(self::$IDX_REQUIRED, function ($value)
        {
            if (isset($value))
            {
                if (is_scalar($value))
                    return (trim($value) !== '');
                else
                    return true;
            }
            return false;
        }, $errorMessage);

        return $this;
    }

    /**
     * @return $this
     */
    public function lengthMin ($length, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must be at least %s characters', $this->label, $length);

        $this->setValidation(self::$IDX_LENGTH_MIN, function ($value) use ($length)
        {
            return (strlen($value) >= $length);
        }, $errorMessage);

        return $this;
    }

    /**
     * @return $this
     */
    public function lengthMax ($length, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must not be more than %s characters', $this->label, $length);

        $this->setValidation(self::$IDX_LENGTH_MAX, function ($value) use ($length)
        {
            return (strlen($value) <= $length);
        }, $errorMessage);

        return $this;
    }

    /**
     * @return $this
     */
    public function lengthBetween ($minLength, $maxLength, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must be between %s to %s characters ', $this->label, $minLength, $maxLength);

        $this->setValidation(self::$IDX_LENGTH_BETWEEN, function ($value) use ($minLength, $maxLength)
        {
            $length = strlen($value);
            return ($length >= $minLength) && ($length <= $maxLength);
        }, $errorMessage);

        return $this;
    }

    /**
     * @return $this
     */
    public function validEmail ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must be a valid email', $this->label);

        $this->setValidation(self::$IDX_VALID_EMAIL, function ($value)
        {
            return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
        }, $errorMessage);

        return $this;
    }

    /**
     * @return $this
     */
    public function onlyNumeric ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must be numeric', $this->label);

        $this->setValidation(self::$IDX_ONLY_NUMERIC, function ($value)
        {
            return is_numeric($value);
        }, $errorMessage);

        return $this;
    }

    /**
     * @return $this
     */
    public function addValidation (Closure $validation, $errorMessage)
    {
        // custom validation start at 'other' index
        if (empty($this->validations))
        {
            $idx = self::$IDX_OTHER;
        }
        else
        {
            // get the last index
            $idx = max(array_keys($this->validations));
            // if the last index is less than the start of 'other' index, start at 'other' index
            if ($idx < self::$IDX_OTHER)
                $idx = self::$IDX_OTHER;
            else // next of the last index
                $idx += 1;
        }

        $this->setValidation($idx, $validation, $errorMessage);

        return $this;
    }

    private function setValidation ($idx, Closure $validation, $errorMessage)
    {
        $this->validations[$idx] = $validation;
        $this->errorMessages[$idx] = $errorMessage;
    }

    /**
     * Run validation.
     * @return bool true if success, false if validation failed
     */
    public function validate ()
    {
        $this->error = null;

        $indexes = array_keys($this->validations);
        sort($indexes);
        foreach ($indexes as $idx)
        {
            if (!$this->validations[$idx]($this->value))
            {
                $errorMessage = $this->errorMessages[$idx];
                if ($errorMessage instanceof Closure)
                    $errorMessage = $errorMessage();

                $this->error = $errorMessage;
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getError ()
    {
        return $this->error;
    }
}
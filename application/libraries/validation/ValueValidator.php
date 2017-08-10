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
    private static $IDX_LENGTH_MIN = 1;
    private static $IDX_LENGTH_MAX = 2;
    private static $IDX_LENGTH_BETWEEN = 3;
    private static $IDX_VALID_EMAIL = 3;
    private static $IDX_NUMERIC = 4;

    /** @var mixed */
    private $value;
    /** @var string */
    private $label;

    /** @var array */
    private $validations;
    /** @var array */
    private $errorMessages;
    /** @var array */
    private $errors;


    public function __construct ($value, $label = 'Value')
    {
        $this->value = $value;
        $this->label = $label;

        $this->validations = array();
        $this->errorMessages = array();
        $this->errors = array();
    }

    /**
     * Validasi gagal jika value sama dengan null, '', atau hanya berisi whitespace.
     * @param string $errorMessage custom error message
     * @return $this
     */
    public function required ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s is required', $this->label);

        $this->addValidation(self::$IDX_REQUIRED, function ()
        {
            if (isset($this->value))
            {
                if (is_scalar($this->value))
                    return (trim($this->value) !== '');
                else
                    return true;
            }
            return false;
        }, $errorMessage);

        return $this;
    }

    /**
     * @param int $length
     * @param string $errorMessage
     * @return $this
     */
    public function lengthMin ($length, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must be at least %s characters', $this->label, $length);

        $this->addValidation(self::$IDX_LENGTH_MIN, function () use ($length)
        {
            return (strlen($this->value) >= $length);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param int $length
     * @param string $errorMessage
     * @return $this
     */
    public function lengthMax ($length, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must not be more than %s characters', $this->label, $length);

        $this->addValidation(self::$IDX_LENGTH_MAX, function () use ($length)
        {
            return (strlen($this->value) <= $length);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param int $minLength
     * @param int $maxLength
     * @param string $errorMessage
     * @return $this
     */
    public function lengthBetween ($minLength, $maxLength, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must be between %s to %s characters ', $this->label, $minLength, $maxLength);

        $this->addValidation(self::$IDX_LENGTH_BETWEEN, function () use ($minLength, $maxLength)
        {
            $length = strlen($this->value);
            return ($length >= $minLength) && ($length <= $maxLength);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function validEmail ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must be a valid email', $this->label);

        $this->addValidation(self::$IDX_VALID_EMAIL, function ()
        {
            return (filter_var($this->value, FILTER_VALIDATE_EMAIL) !== false);
        }, $errorMessage);

        return $this;
    }

    public function numeric ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s must be numeric', $this->label);

        $this->addValidation(self::$IDX_NUMERIC, function ()
        {
            return is_numeric($this->value);
        }, $errorMessage);

        return $this;
    }

    private function addValidation ($idx, Closure $validation, $errorMessage)
    {
        if (!isset($this->validations[$idx]))
        {
            $this->validations[$idx] = $validation;
        }
        $this->errorMessages[$idx] = $errorMessage;
    }

    /**
     * Run validation.
     * @return bool true if success, false if validation failed
     */
    public function validate ()
    {
        $this->errors = array();

        foreach ($this->validations as $idx => $validation)
        {
            if (!$validation())
            {
                $this->errors[] = $this->errorMessages[$idx];
            }
        }

        return empty($this->errors);
    }

    /**
     * @return string
     */
    public function getError ()
    {
        if (empty($this->errors))
            return '';
        else
            return $this->errors[0];
    }

    /**
     * @return array
     */
    public function getAllErrors ()
    {
        return $this->errors;
    }
}
<?php

require_once('Validation.php');

/**
 * @author Ray Naldo
 */
class ValueValidator implements Validation
{
    private static $IDX_NOT_EMPTY = 0;

    // comparing content type, same validation index
    private static $IDX_VALID_EMAIL = 1;
    private static $IDX_VALID_DATETIME = 1;
    private static $IDX_ONLY_BOOLEAN = 1;
    private static $IDX_ONLY_INTEGER = 1;
    private static $IDX_ONLY_FLOAT = 1;
    private static $IDX_ONLY_STRING = 1;
    // can be string/integer/float
    private static $IDX_ONLY_NUMERIC = 1;
    private static $IDX_ONLY_ARRAY = 1;
    private static $IDX_ONLY_ONE_OF = 1;

    // comparing content attributes
    private static $IDX_LENGTH_MIN = 6;
    private static $IDX_LENGTH_MAX = 6;
    private static $IDX_LENGTH_BETWEEN = 6;
    private static $IDX_LENGTH_EQUALS = 6;

    // other
    private static $IDX_OTHER = 11;

    /** @var mixed */
    private $value;
    /** @var string */
    private $label;

    private $nullable = false;

    /** @var array */
    private $validations = array();
    /** @var array */
    private $errorMessages = array();
    /** @var string */
    private $error;


    /**
     * @param mixed $value
     * @param string $label
     */
    public function __construct ($value, $label = 'Value')
    {
        $this->value = $value;
        $this->label = $label;

        $this->resetError();
    }

    /**
     * @param mixed $value
     */
    protected function setValue ($value)
    {
        $this->value = $value;
        $this->resetError();
    }

    public function nullable ()
    {
        $this->nullable = true;
        return $this;
    }

    /**
     * Validation failed if value equals to null, empty string, or empty array.
     * @param string $errorMessage custom error message
     * @return $this
     */
    public function notEmpty ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must not be empty';

        $this->setValidation(self::$IDX_NOT_EMPTY, function ($value)
        {
            // extra checks so 0, 0.0, "0", and false are not considered as empty by empty() method
            return !empty($value) || is_numeric($value) || is_bool($value);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param int $min min number of bytes of the string or min number of elements in the array (or count on Countable)
     * @param string $errorMessage extra placeholders: {min}
     * @return $this
     */
    public function lengthMin ($min, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} length must be at least {min}';

        $errorMessage = $this->formatMessage($errorMessage, [
            '{min}' => $min
        ]);

        $this->setValidation(self::$IDX_LENGTH_MIN, function ($value) use ($min)
        {
            if (is_scalar($value))
                $length = strlen($value);
            else if (is_array($value) || (is_object($value) && $value instanceof Countable))
                $length = count($value);
            else
                return false;

            return ($length >= $min);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param int $max max number of bytes the string has or max number of elements in the array (or count on Countable)
     * @param string $errorMessage extra placeholders: {max}
     * @return $this
     */
    public function lengthMax ($max, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} length must not be more than {max}';

        $errorMessage = $this->formatMessage($errorMessage, [
            '{max}' => $max
        ]);

        $this->setValidation(self::$IDX_LENGTH_MAX, function ($value) use ($max)
        {
            if (is_scalar($value))
                $length = strlen($value);
            else if (is_array($value) || (is_object($value) && $value instanceof Countable))
                $length = count($value);
            else
                return false;

            return ($length <= $max);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param int $min
     * @param int $max
     * @param string $errorMessage extra placeholders: {min}, {max}
     * @return $this
     */
    public function lengthBetween ($min, $max, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} length must be between {min} to {max}';

        $errorMessage = $this->formatMessage($errorMessage, [
            '{min}' => $min,
            '{max}' => $max
        ]);

        $this->setValidation(self::$IDX_LENGTH_BETWEEN, function ($value) use ($min, $max)
        {
            if (is_scalar($value))
                $length = strlen($value);
            else if (is_array($value) || (is_object($value) && $value instanceof Countable))
                $length = count($value);
            else
                return false;

            return ($length >= $min) && ($length <= $max);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param int $length
     * @param string $errorMessage extra placeholders: {length}
     * @return $this
     */
    public function lengthEquals ($length, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be exactly {length} characters';

        $errorMessage = $this->formatMessage($errorMessage, [
            '{length}' => $length
        ]);

        $this->setValidation(self::$IDX_LENGTH_EQUALS, function ($value) use ($length)
        {
            if (is_scalar($value))
                $valueLength = strlen($value);
            else if (is_array($value) || (is_object($value) && $value instanceof Countable))
                $valueLength = count($value);
            else
                return false;

            return ($valueLength === $length);
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
            $errorMessage = '{label} must be a valid email';

        $this->setValidation(self::$IDX_VALID_EMAIL, function ($value)
        {
            return (filter_var($value, FILTER_VALIDATE_EMAIL) !== false);
        }, $errorMessage);

        return $this;
    }

    /**
     * Validate if value is a valid ISO-8601 date in the format '[YYYY]-[MM]-[DD]'.
     * Reference: https://www.w3.org/TR/NOTE-datetime
     * @param string $errorMessage
     * @return $this
     */
    public function validDate ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be a valid date in the format: YYYY-MM-DD';

        $this->setValidation(self::$IDX_VALID_DATETIME, function ($value)
        {
            /*
             * 2017-13-01 invalid
             * 2017-02-29 invalid
             * 2020-02-29 valid
            */
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if ($date === false)
                return false;
            else
                return ($date->format('Y-m-d') === $value);
        }, $errorMessage);

        return $this;
    }

    /**
     * Validate if value is a valid ISO-8601 date & time with timezone in the format '[YYYY]-[MM]-[DD]T[hh]:[mm]:[ss][TZD]'.
     * Reference: https://www.w3.org/TR/NOTE-datetime
     * @param string $errorMessage
     * @return $this
     */
    public function validDateTime ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be a valid date/time in the format: [YYYY]-[MM]-[DD]T[hh]:[mm]:[ss][TZD]';

        $this->setValidation(self::$IDX_VALID_DATETIME, function ($value)
        {
            /*
             * 2017-12-01T15:00:31+0000 invalid
             * 2017-12-01T15:00:31+01 invalid
             * 2017-12-01T15:00:31+00:00 valid
             * 2017-12-01T15:00:31-02:00 valid
             * 2017-12-01T15:00:31Z valid
             */
            $dateTime = DateTime::createFromFormat(DateTime::ISO8601, $value);
            if ($dateTime === false)
                return false;
            else
            {
                $lastIdx = strlen($value)-1;
                if ($value[$lastIdx] == 'Z')
                    $value = substr($value, 0, $lastIdx).'+0000';

                return ($dateTime->format(DateTime::ISO8601) == $value);
            }
        }, $errorMessage);

        return $this;
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function onlyBoolean ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be true/false';

        $this->setValidation(self::$IDX_ONLY_BOOLEAN, function ($value)
        {
            return is_bool($value);
        }, $errorMessage);

        return $this;
    }

    /**
     * Validation pass only if value is truly an integer (not string)
     * @param string $errorMessage
     * @return $this
     */
    public function onlyInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be integer number';

        $this->setValidation(self::$IDX_ONLY_INTEGER, function ($value)
        {
            return is_int($value);
        }, $errorMessage);

        return $this;
    }

    /**
     * Validation pass only if value is truly an integer (not string) and >= 0
     * @param string $errorMessage
     * @return $this
     */
    public function onlyPositiveInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be positive integer number';

        $this->setValidation(self::$IDX_ONLY_INTEGER, function ($value)
        {
            return is_int($value) && ($value >= 0);
        }, $errorMessage);

        return $this;
    }

    /**
     * Validation pass only if value is truly a float (not string)
     * @param string $errorMessage
     * @return $this
     */
    public function onlyFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be float number';

        $this->setValidation(self::$IDX_ONLY_FLOAT, function ($value)
        {
            return is_int($value) || is_float($value);
        }, $errorMessage);

        return $this;
    }

    /**
     * Validation pass only if value is truly a float (not string) and >= 0
     * @param string $errorMessage
     * @return $this
     */
    public function onlyPositiveFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be positive float number';

        $this->setValidation(self::$IDX_ONLY_FLOAT, function ($value)
        {
            return (is_int($value) || is_float($value)) && ($value >= 0);
        }, $errorMessage);

        return $this;
    }

    /**
     * Validation pass only if value is truly an integer (not string)
     * @param string $errorMessage
     * @return $this
     */
    public function onlyString ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be text/string';

        $this->setValidation(self::$IDX_ONLY_STRING, function ($value)
        {
            return is_string($value);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function onlyNumeric ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be number or numeric string';

        $this->setValidation(self::$IDX_ONLY_NUMERIC, function ($value)
        {
            return is_numeric($value);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function onlyNumericInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be integer number or integer string';

        $this->setValidation(self::$IDX_ONLY_NUMERIC, function ($value)
        {
            if (is_numeric($value))
            {
                // workaround to make "01" returns false
                // compare casted value string with original value string
                // false integer string will return false i.e. "01" != "1"
                $value = (string) $value;
                $number = $value+0; // convert numeric string to int or float
                $strValue = (string) $number;

                return ($strValue === $value) && is_int($number);
            }

            return false;
        }, $errorMessage);

        return $this;
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function onlyPositiveNumericInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be positive integer number or positive integer string';

        $this->setValidation(self::$IDX_ONLY_NUMERIC, function ($value)
        {
            if (is_numeric($value))
            {
                // workaround to make "01" returns false
                // compare casted value string with original value string
                // false integer string will return false i.e. "01" != "1"
                $value = (string) $value;
                $number = $value+0; // convert numeric string to int or float
                $strValue = (string) $number;

                return ($strValue === $value) && is_int($number) && ($number >= 0);
            }

            return false;
        }, $errorMessage);

        return $this;
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function onlyNumericFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be float number or float string';

        $this->setValidation(self::$IDX_ONLY_NUMERIC, function ($value)
        {
            if (is_numeric($value))
            {
                // workaround to make "01.2" returns false
                // compare casted value string with original value string
                // false float string will return false i.e. "01.2" != "1.2"
                $value = (string) $value;
                $number = $value+0; // convert numeric string to int or float
                $strValue = (string) $number;

                return ($strValue === $value) && (is_int($number) || is_float($number));
            }

            return false;
        }, $errorMessage);

        return $this;
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function onlyPositiveNumericFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be positive float number or positive float string';

        $this->setValidation(self::$IDX_ONLY_NUMERIC, function ($value)
        {
            if (is_numeric($value))
            {
                /*
                 * workaround to make "01.2" returns false
                 * compare casted value string with original value string
                 * false float string will return false i.e. "01.2" != "1.2"
                 */
                $value = (string) $value;
                $number = $value+0; // convert numeric string to int or float
                $strValue = (string) $number;

                /*
                 * workaround to make "100.0", "2.50" returns true
                 */
                // apply only if decimal point (".") exists
                $decimalIdx = strpos($value, '.');
                if ($decimalIdx !== false)
                {
                    // remove "0" at the end of the value (fractional-part)
                    $value = rtrim($value, '0');
                    // remove decimal point if no fractional-part left
                    if (strlen($value) <= ($decimalIdx+1))
                        $value = substr($value, 0, $decimalIdx);
                }

                return ($strValue === $value) && (is_int($number) || is_float($number)) && ($number >= 0);
            }

            return false;
        }, $errorMessage);

        return $this;
    }


    /**
     * Validation failed if value equals to null, '', or only whitespaces.
     * @param string $errorMessage custom error message
     * @return $this
     */
    public function onlyArrayOfAssociatives ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be array of associative arrays';

        $this->setValidation(self::$IDX_ONLY_ARRAY, function ($data)
        {
            if (is_array($data))
            {
                foreach ($data as $row)
                {
                    if (is_array($row))
                    {
                        foreach ($row as $key => $value)
                        {
                            if (!is_string($key))
                                return false;
                        }
                    }
                    else
                        return false;
                }
                return true;
            }
            return false;
        }, $errorMessage);

        return $this;
    }

    /**
     * Validation failed if value equals to null, '', or only whitespaces.
     * @param string $errorMessage custom error message
     * @return $this
     */
    public function onlyArrayOfObjects ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be array of objects';

        $this->setValidation(self::$IDX_ONLY_ARRAY, function ($data)
        {
            if (is_array($data))
            {
                foreach ($data as $row)
                {
                    if (!is_object($row))
                        return false;
                }
                return true;
            }
            return false;
        }, $errorMessage);

        return $this;
    }

    /**
     * Validation pass only if value is one of the specified values.
     * @param array $values
     * @param string $errorMessage
     * @return $this
     */
    public function onlyOneOf (array $values, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = "{label} must be one of: '".implode("','", $values)."'";

        $errorMessage = $this->formatMessage($errorMessage, [
            '{values}' => "'".implode("','", $values)."'"
        ]);

        $this->setValidation(self::$IDX_ONLY_ONE_OF, function ($value) use ($values)
        {
            return in_array($value, $values, true);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param Closure $validation a Closure with one parameter: the value being validated
     * @param string|Closure $errorMessage error message or function to return error message (called on validation failed)
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

    protected function setValidation ($idx, Closure $validation, $errorMessage)
    {
        $this->validations[$idx] = $validation;
        $this->errorMessages[$idx] = $errorMessage;
    }

    public function validate ()
    {
        $this->resetError();

        if (is_null($this->value) && $this->nullable)
            return true;

        $indexes = array_keys($this->validations);
        sort($indexes);
        foreach ($indexes as $idx)
        {
            if (!$this->validations[$idx]($this->value))
            {
                $errorMessage = $this->errorMessages[ $idx ];
                if ($errorMessage instanceof Closure)
                    $errorMessage = $errorMessage();

                $this->setError($this->formatErrorMessage($errorMessage));
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $errorMessage
     * @return string
     */
    protected function formatErrorMessage ($errorMessage)
    {
        $replacements = [
            '{label}' => $this->label
        ];
        if (is_scalar($this->value))
            $replacements['{value}'] = $this->value;

        return $this->formatMessage($errorMessage, $replacements);
    }

    private function formatMessage ($message, array $replacements)
    {
        if (empty($message))
            return '';
        else
            return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    protected function resetError ()
    {
        $this->setError('');
    }

    /**
     * @param string $error
     */
    protected function setError ($error)
    {
        $this->error = $error;
    }

    public function getError ()
    {
        return $this->error;
    }
}
<?php

require_once('Validation.php');

/**
 * @author Ray Naldo
 */
class ValueValidator implements Validation
{
    private static $IDX_REQUIRED = 0;
    private static $IDX_OPTIONAL = 0;

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
    private static $IDX_LENGTH_MAX = 7;
    private static $IDX_LENGTH_BETWEEN = 8;
    private static $IDX_LENGTH_EQUALS = 8;

    // other
    private static $IDX_OTHER = 11;

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

    private $optional = false;


    public function __construct ($value, $label = 'Value')
    {
        $this->value = $value;
        $this->label = $label;

        $this->validations = array();
        $this->errorMessages = array();
        $this->error = '';
    }

    /**
     * @param mixed $value
     */
    public function setValue ($value)
    {
        $this->value = $value;
        $this->error = '';
    }

    /**
     * Validation failed if value equals to null, '', or only whitespaces.
     * @param string $errorMessage custom error message
     * @return $this
     */
    public function required ($errorMessage = null)
    {
        $this->optional = false;

        if (is_null($errorMessage))
            $errorMessage = '{label} is required';

        $this->setValidation(self::$IDX_REQUIRED, function ($value)
        {
            if (isset($value))
            {
                if (is_scalar($value) && !is_bool($value))
                    return (trim($value) !== '');
                else if (is_array($value))
                    return (count($value) > 0);
                else
                    return true;
            }
            return false;
        }, $errorMessage);

        return $this;
    }

    /**
     * Mark this validation as optional.
     * @return $this
     */
    public function optional ()
    {
        $this->optional = true;

        $this->setValidation(self::$IDX_OPTIONAL, function ($value)
        {
            return true;
        }, null);

        return $this;
    }

    /**
     * @param int $min
     * @param string $errorMessage extra placeholders: {min}
     * @return $this
     */
    public function lengthMin ($min, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must be at least {min} characters';

        $errorMessage = $this->formatMessage($errorMessage, [
            '{min}' => $min
        ]);

        $this->setValidation(self::$IDX_LENGTH_MIN, function ($value) use ($min)
        {
            return (strlen($value) >= $min);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param int $max
     * @param string $errorMessage extra placeholders: {max}
     * @return $this
     */
    public function lengthMax ($max, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} must not be more than {max} characters';

        $errorMessage = $this->formatMessage($errorMessage, [
            '{max}' => $max
        ]);

        $this->setValidation(self::$IDX_LENGTH_MAX, function ($value) use ($max)
        {
            return (strlen($value) <= $max);
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
            $errorMessage = '{label} must be between {min} to {max} characters';

        $errorMessage = $this->formatMessage($errorMessage, [
            '{min}' => $min,
            '{max}' => $max
        ]);

        $this->setValidation(self::$IDX_LENGTH_BETWEEN, function ($value) use ($min, $max)
        {
            $length = strlen($value);
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
            return (strlen($value) === $length);
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
     * Validate if value is a valid ISO-8601 date & time in the format '[YYYY]-[MM]-[DD]'.
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
     * Validate if value is a valid ISO-8601 UTC date & time in the format '[YYYY]-[MM]-[DD]T[hh]:[mm]:[ss][TZD]'.
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
            $dateTime = DateTime::createFromFormat('Y-m-d\TH:i:sP', $value);
            if ($dateTime === false)
                return false;
            else
            {
                $lastIdx = strlen($value)-1;
                if ($value[$lastIdx] == 'Z')
                    $value = substr($value, 0, $lastIdx).'+00:00';

                return ($dateTime->format('Y-m-d\TH:i:sP') == $value);
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
                return !empty($data);
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
                return !empty($data);
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
            return in_array($value, $values);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param Closure $validation
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

    private function setValidation ($idx, Closure $validation, $errorMessage)
    {
        $valueValidation = function ($value) use ($validation)
        {
            if (is_null($value) && $this->optional)
                return true;

            return $validation($value);
        };

        $this->validations[$idx] = $valueValidation;
        $this->errorMessages[$idx] = $errorMessage;
    }

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

                $replacements = [
                    '{label}' => $this->label
                ];
                if (is_scalar($this->value))
                    $replacements['{value}'] = $this->value;

                $this->error = $this->formatMessage($errorMessage, $replacements);
                return false;
            }
        }

        return true;
    }

    private function formatMessage ($message, array $replacements)
    {
        if (empty($message))
            return '';
        else
            return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    public function getError ()
    {
        return $this->error;
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once('validation/InputValidation.php');

/**
 * @author Ray Naldo
 */
class Rest_validation extends InputValidation
{
    /**
     * @param string $value
     * @param string $errorMessage
     * @return int the integer value
     * @throws ResourceNotFoundException if value is not an integer
     */
    public function tryParseIntegerOrNotFound ($value, $errorMessage)
    {
        $valid = $this->forValue($value)->onlyNumericInteger()->validate();
        if ($valid)
            return (int) $value;
        else
            throw new ResourceNotFoundException($errorMessage, $this->getDomain());
    }

    public function validatePositiveInteger ($value, Exception $exception)
    {
        $valid = $this->forValue($value)->onlyPositiveInteger()->validate();
        if (!$valid)
            throw $exception;
    }

    public function validatePositiveFloat ($value, Exception $exception)
    {
        $valid = $this->forValue($value)->onlyPositiveFloat()->validate();
        if (!$valid)
            throw $exception;
    }
}
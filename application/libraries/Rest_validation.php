<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once('validation/InputValidation.php');
include_once('validation_ci/ValidatorFactoryCI.php');

/**
 * @author Ray Naldo
 */
class Rest_validation extends InputValidation
{
    public function __construct ()
    {
        parent::__construct(new ValidatorFactoryCI());
    }

    /**
     * @param string $value
     * @param string $errorMessage
     * @return int the integer value
     * @throws ResourceNotFoundException if value is not an integer
     */
    public function tryParseIntegerOrNotFound ($value, $errorMessage)
    {
        try
        {
            return $this->tryParseInteger($value, $errorMessage);
        }
        catch (BadValueException $e)
        {
            throw new ResourceNotFoundException($errorMessage, $this->getDomain());
        }
    }

    /**
     * @param string $value
     * @param string $errorMessage
     * @return int the integer value
     * @throws BadValueException
     */
    public function tryParseInteger ($value, $errorMessage)
    {
        $valid = $this->forValue($value)->onlyNumericInteger()->validate();
        if ($valid)
            return (int) $value;
        else
            throw new BadValueException($errorMessage, $this->getDomain());
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

    public function validateOrNotFound ()
    {
        try
        {
            $this->validate();
        }
        catch (Exception $e)
        {
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->getDomain()), $this->getDomain());
        }
    }
}
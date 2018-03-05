<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once('validation/InputValidation.php');
include_once('validation_ci/ValidatorFactoryCI.php');

/**
 * @author Ray Naldo
 */
class Rest_validation extends InputValidation
{
    private $domain = 'Validation';

    public function __construct ()
    {
        parent::__construct(new ValidatorFactoryCI());
    }

    /**
     * @param mixed $domain
     */
    public function setDomain ($domain)
    {
        $this->domain = $domain;
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
            throw new ResourceNotFoundException($errorMessage, $this->domain);
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
            throw new BadValueException($errorMessage, $this->domain);
    }

    public function validateOrNotFound ()
    {
        try
        {
            $this->validate();
        }
        catch (Exception $e)
        {
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        }
    }

    /**
     * @throws BadArrayException
     * @throws BadBatchArrayException
     * @throws BadValueException
     */
    public function validate ()
    {
        try
        {
            parent::validate();
        }
        catch (InvalidArrayException $e)
        {
            throw new BadArrayException($e->getAllErrors(), $this->domain);
        }
        catch (InvalidBatchArrayException $e)
        {
            throw new BadBatchArrayException($e->getBatchErrors(), $this->domain);
        }
        catch (InvalidValueException $e)
        {
            throw new BadValueException($e->getMessage(), $this->domain);
        }
    }
}
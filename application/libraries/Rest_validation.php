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
        catch (BadValueApiException $e)
        {
            throw new ResourceNotFoundException($errorMessage, $this->domain);
        }
    }

    /**
     * @param string $value
     * @param string $errorMessage
     * @return int the integer value
     * @throws BadValueApiException
     */
    public function tryParseInteger ($value, $errorMessage)
    {
        $valid = $this->forValue($value)->onlyNumericInteger()->validate();
        if ($valid)
            return (int) $value;
        else
            throw new BadValueApiException($errorMessage, $this->domain);
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
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $this->domain), $this->domain);
        }
    }

    public function validate ()
    {
        try
        {
            parent::validate();
        }
        catch (BadArrayException $e)
        {
            throw new BadArrayApiException($e->getAllErrors(), $this->domain);
        }
        catch (BadBatchArrayException $e)
        {
            throw new BadBatchArrayApiException($e->getBatchErrors(), $this->domain);
        }
        catch (BadValueException $e)
        {
            throw new BadValueApiException($e->getMessage(), $this->domain);
        }
    }


}
<?php

/**
 * @author Ray Naldo
 */
class InvalidBatchArrayException extends Exception
{
    private $errors;

    public function __construct (array $errors)
    {
        parent::__construct();
        $this->errors = $errors;
    }

    /**
     * Get error messages.
     * @return array
     */
    public function getBatchErrors ()
    {
        return $this->errors;
    }
}
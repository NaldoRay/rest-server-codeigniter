<?php

/**
 * @author Ray Naldo
 */
class InvalidArrayException extends Exception
{
    private $errors;

    public function __construct (array $errors)
    {
        parent::__construct();
        $this->errors = $errors;
    }

    /**
     * @return array all error messages
     */
    public function getAllErrors ()
    {
        return $this->errors;
    }
}
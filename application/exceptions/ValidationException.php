<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User: RN
 * Date: 6/13/2017
 * Time: 13:28
 */
class ValidationException extends Exception
{
    private $errors;

    public function __construct (array $errors)
    {
        parent::__construct();
        $this->errors = $errors;
    }

    public function getValidationErrors ()
    {
        return $this->errors;
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User: RN
 * Date: 6/13/2017
 * Time: 13:28
 */
class ValidationException extends Exception
{
    private $domain;
    private $errors;

    public function __construct (array $errors, $domain = 'API')
    {
        parent::__construct();
        $this->domain = $domain;
        $this->errors = $errors;
    }

    public function getDomain ()
    {
        return $this->domain;
    }

    /**
     * Get validation errors.
     * @return array
     */
    public function getErrors ()
    {
        return $this->errors;
    }
}
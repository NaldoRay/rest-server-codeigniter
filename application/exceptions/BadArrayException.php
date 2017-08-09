<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User: RN
 * Date: 6/13/2017
 * Time: 13:28
 */
class BadArrayException extends Exception
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
     * Get error messages.
     * @return array
     */
    public function getAllErrors ()
    {
        return $this->errors;
    }
}
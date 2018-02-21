<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class BadArrayException extends Exception
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
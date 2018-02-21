<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class BadArrayApiException extends ApiException
{
    private $errors;

    public function __construct (array $errors, $domain = 'API')
    {
        parent::__construct('', $domain);
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
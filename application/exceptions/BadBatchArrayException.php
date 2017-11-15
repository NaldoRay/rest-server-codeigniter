<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class BadBatchArrayException extends ApiException
{
    private $errors;

    public function __construct (array $errors, $domain = 'API')
    {
        parent::__construct('', $domain);
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
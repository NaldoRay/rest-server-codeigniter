<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User: RN
 * Date: 8/9/2017
 * Time: 15:32
 */
class BadValueException extends Exception
{
    private $domain;

    public function __construct ($error, $domain = 'API')
    {
        parent::__construct($error);
        $this->domain = $domain;
    }

    public function getDomain ()
    {
        return $this->domain;
    }
}
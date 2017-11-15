<?php

/**
 * @author Ray Naldo
 */
class ApiException extends Exception
{
    private $domain;

    /**
     * ApiException constructor.
     * @param $domain
     * @param $message
     */
    public function __construct ($message = '', $domain = 'API')
    {
        parent::__construct($message);
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getDomain ()
    {
        return $this->domain;
    }
}
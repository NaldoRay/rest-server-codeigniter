<?php

require_once 'WebResponse.php';

/**
 * @author Ray Naldo
 */
class WebException extends Exception
{
    private $response;

    public function __construct (WebResponse $response)
    {
        parent::__construct();
        $this->response = $response;
    }

    public function getResponse ()
    {
        return $this->response;
    }
}
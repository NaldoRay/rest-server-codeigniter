<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class InvalidValueException extends Exception
{
    public function __construct ($message = '')
    {
        parent::__construct($message);
    }
}
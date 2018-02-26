<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class NotSupportedException extends RuntimeException
{
    public function __construct ($message = 'Not supported')
    {
        parent::__construct($message);
    }
}
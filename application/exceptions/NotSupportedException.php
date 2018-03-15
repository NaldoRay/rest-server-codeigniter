<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class NotSupportedException extends ApiException
{
    public function __construct ($message = 'Not supported', $domain = 'API')
    {
        parent::__construct($message, $domain);
    }
}
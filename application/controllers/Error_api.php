<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class Error_api extends MY_REST_Controller
{
    public function throwNotFound ()
    {
        $this->respondNotFound('Resource not found');
    }
}
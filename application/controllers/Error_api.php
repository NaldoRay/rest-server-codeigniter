<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'MY_REST_Controller.php';

/**
 * User: RN
 * Date: 5/31/2017
 * Time: 11:23
 */
class Error_api extends MY_REST_Controller
{
    public function throwNotFound ()
    {
        $this->respondNotFound('Resource not found');
    }
}
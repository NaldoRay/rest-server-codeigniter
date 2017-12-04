<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Extends {@see CI_URI} class to auto-rawurldecode URI.
 * @author Ray Naldo
 */
class MY_URI extends CI_URI
{
    public function filter_uri (&$str)
    {
        parent::filter_uri($str);
        $str = rawurldecode($str);
    }
}
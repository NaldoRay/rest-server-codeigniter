<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
Author: Ray Naldo
 */
defined('WEEKLY') OR define ('WEEKLY', 1);
defined('MONTHLY') OR define ('MONTHLY', 2);
defined('YEARLY') OR define ('YEARLY', 3);
defined('LOG_PATH') OR define ('LOG_PATH', FCPATH.'logs/');

if (!function_exists('logQuery'))
{
    /**
     * Log query ke file.
     *
     * @param string $query query to be logged
     * @param int $category to group logs; set MONTHLY (default), or YEARLY
     */
    function logQuery ($query, $category = MONTHLY)
    {
        $CI =& get_instance();

        switch ($category)
        {
            case YEARLY:
                $date = date("Y");
                break;
            case WEEKLY:
                $date = sprintf('%s-week%s', date('Ym'), date('W'));
                break;
            default:
                $date = date("Ym");
                break;
        }
        $appName = $CI->config->item('app_name');
        $fileName = $appName.'_'.$date.'.txt';

        $ipAddress = $CI->input->ip_address();
        $timestamp = date("Y-m-d H:i:s");
        $logText = '['.$timestamp.'|'.$ipAddress.'|'.$query.']'.PHP_EOL;

        $filePath = LOG_PATH.$fileName;
        write_file($filePath, $logText, 'a');
    }
}

if (!function_exists('logFailedQuery'))
{
    function logFailedQuery ($query, $category = MONTHLY)
    {
        $CI =& get_instance();

        switch ($category)
        {
            case YEARLY:
                $date = date("Y");
                break;
            case WEEKLY:
                $date = sprintf('%s-week%s', date('Ym'), date('W'));
                break;
            default:
                $date = date("Ym");
                break;
        }
        $appName = $CI->config->item('app_name');
        $fileName = $appName.'_'.$date.'_failed.txt';
        $filePath = LOG_PATH.$fileName;

        $ipAddress = $CI->input->ip_address();
        $timestamp = date("Y-m-d H:i:s");
        $logText = '['.$timestamp.'|'.$ipAddress.'|'.$query.']'.PHP_EOL;

        write_file($filePath, $logText, 'a');
    }
}

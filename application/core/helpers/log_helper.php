<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Author: Ray Naldo
 */
defined('LOG_DAILY') OR define ('LOG_DAILY', 1);
defined('LOG_WEEKLY') OR define ('LOG_WEEKLY', 2);
defined('LOG_MONTHLY') OR define ('LOG_MONTHLY', 3);
defined('LOG_YEARLY') OR define ('LOG_YEARLY', 4);
defined('LOG_PATH') OR define ('LOG_PATH', APPPATH.'../logs/');


if (!function_exists('logQuery'))
{
    /**
     * Log query ke file.
     *
     * @param string $query query to be logged
     * @param int $category to group logs; set LOG_MONTHLY (default), or LOG_YEARLY
     */
    function logQuery ($query, $category = null)
    {
        $CI =& get_instance();

        if (empty($category))
        {
            // read log category from config
            $categoryConfig = $CI->config->item('app_log_category');
            if (empty($categoryConfig))
            {
                // default to MONTHLY
                $category = LOG_MONTHLY;
            }
            else
            {
                $category = $categoryConfig;
            }
        }

        switch ($category)
        {
            case LOG_YEARLY:
                $date = date("Y");
                break;
            case LOG_MONTHLY:
                $date = date("Ym");
                break;
            case LOG_WEEKLY:
                $date = sprintf('%s-week%s', date('Ym'), date('W'));
                break;
            default:
                $date = date("Ymd");
                break;
        }

        $prefix = $CI->config->item('app_name');
        if (empty($prefix))
            $prefix = 'log';
        $fileName = $prefix.'_'.$date.'.txt';

        $ipAddress = $CI->input->ip_address();
        $timestamp = date("Y-m-d H:i:s");
        $logText = '['.$timestamp.'|'.$ipAddress.'|'.$query.']'.PHP_EOL;

        $filePath = LOG_PATH.$fileName;
        write_file($filePath, $logText, 'a');
    }
}

if (!function_exists('logFailedQuery'))
{
    function logFailedQuery ($query, $category = null)
    {
        $CI =& get_instance();

        if (empty($category))
        {
            // read log category from config
            $categoryConfig = $CI->config->item('app_log_category');
            if (empty($categoryConfig))
            {
                // default to MONTHLY
                $category = LOG_MONTHLY;
            }
            else
            {
                $category = $categoryConfig;
            }
        }

        switch ($category)
        {
            case LOG_YEARLY:
                $date = date("Y");
                break;
            case LOG_MONTHLY:
                $date = date("Ym");
                break;
            case LOG_WEEKLY:
                $date = sprintf('%s-week%s', date('Ym'), date('W'));
                break;
            default:
                $date = date("Ymd");
                break;
        }

        $prefix = $CI->config->item('app_name');
        if (empty($prefix))
            $prefix = 'log';
        $fileName = $prefix.'_'.$date.'_failed.txt';
        $filePath = LOG_PATH.$fileName;

        $ipAddress = $CI->input->ip_address();
        $timestamp = date("Y-m-d H:i:s");
        $logText = '['.$timestamp.'|'.$ipAddress.'|'.$query.']'.PHP_EOL;

        write_file($filePath, $logText, 'a');
    }
}

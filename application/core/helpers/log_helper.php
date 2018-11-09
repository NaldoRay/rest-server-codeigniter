<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Author: Ray Naldo
 */

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

        $logPath = $CI->config->item('app_query_log_path');

        if (empty($category))
        {
            // read log category from config
            $categoryConfig = $CI->config->item('app_query_log_category');
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
        $clientIpAddress = $CI->input->get_request_header('X-Client-IP');
        if (!is_null($clientIpAddress))
            $ipAddress = sprintf('%s@%s', $clientIpAddress, $ipAddress);

        $timestamp = date("Y-m-d H:i:s");
        $logText = '['.$timestamp.'|'.$ipAddress.'|'.$query.']';

        $filePath = $logPath . $fileName;
        $logLine = $logText . PHP_EOL;
        write_file($filePath, $logLine, 'a');
    }
}

if (!function_exists('logFailedQuery'))
{
    function logFailedQuery ($query, $category = null)
    {
        $CI =& get_instance();

        $logPath = $CI->config->item('app_query_log_path');

        if (empty($category))
        {
            // read log category from config
            $categoryConfig = $CI->config->item('app_query_log_category');
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

        $ipAddress = $CI->input->ip_address();
        $clientIpAddress = $CI->input->get_request_header('X-Client-IP');
        if (!is_null($clientIpAddress))
            $ipAddress = sprintf('%s@%s', $clientIpAddress, $ipAddress);

        $timestamp = date("Y-m-d H:i:s");
        $logText = '['.$timestamp.'|'.$ipAddress.'|'.$query.']'.PHP_EOL;

        $filePath = $logPath . $fileName;
        $logLine = $logText . PHP_EOL;
        write_file($filePath, $logLine, 'a');
    }
}

if (!function_exists('logContextError'))
{
    /**
     * Log request ke file.
     *
     * @param int $statusCode
     * @param mixed $response
     * @param int $category to group logs; set LOG_MONTHLY (default), or LOG_YEARLY
     */
    function logContextError ($statusCode, $response, $category = null)
    {
        $CI =& get_instance();

        $logPath = $CI->config->item('app_context_error_log_path');

        if (empty($category))
        {
            // read log category from config
            $categoryConfig = $CI->config->item('app_context_error_log_category');
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

        /*
         * Log line
         */
        $date = date(DATE_ISO8601);
        $method = $CI->input->method(true);

        $uri = $CI->uri->uri_string();
        $queryString = $CI->input->server('QUERY_STRING');
        if (!empty($queryString))
            $uri .= '?' . $queryString;

        $sourceIP = $CI->input->ip_address();

        $requestHeaders = $CI->input->request_headers();
        if (isset($requestHeaders['Authorization']))
        {
            $parts = explode(' ', $requestHeaders['Authorization']);
            if (count($parts) > 1)
                $requestHeaders['Authorization'] = $parts[0] . ' ...';
            else
                $requestHeaders['Authorization'] = '...';
        }

        $requestBody = json_decode($CI->input->raw_input_stream, true);

        $logText = json_encode([
            'date' => $date,
            'method' => $method,
            'uri' => $uri,
            'statusCode' => $statusCode,
            'sourceIP' => $sourceIP,
            'requestHeaders' => $requestHeaders,
            'requestBody' => $requestBody,
            'responseBody' => $response
        ]);

        $filePath = $logPath . $fileName;
        $logLine = $logText . PHP_EOL;
        write_file($filePath, $logLine, 'a');
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

defined('MONTHLY') OR define ('MONTHLY', 1);
defined('YEARLY') OR define ('YEARLY', 2);
defined('LOG_PATH') OR define ('LOG_PATH', APPPATH.'../logs/');

if (!function_exists('logQuery'))
{
	/**
	 * Log query ke file.
	 *
	 * @param $query query yang ingin di-log (string)
	 * @param $category pengelompokan log file. Set MONTHLY (per bulan)(default), atau YEARLY (per tahun)
	 */
	function logQuery ($query, $category = MONTHLY)
	{
		$CI =& get_instance();

		$appName = $CI->config->item('app_name');
		if ($category === YEARLY)
			$date = date("Y");
		else
			$date = date("Ym");
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

		$appName = $CI->config->item('app_name');
		if ($category === YEARLY)
			$date = date("Y");
		else
			$date = date("Ym");
		
		$fileName = $appName.'_'.$date.'_failed.txt';
		$filePath = LOG_PATH.$fileName;

		$ipAddress = $CI->input->ip_address();
		$timestamp = date("Y-m-d H:i:s");
		$logText = '['.$timestamp.'|'.$ipAddress.'|'.$query.']'.PHP_EOL;
		
		write_file($filePath, $logText, 'a');
	}
}


/*
	RN @ 2015-2016
*/
?>
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Author: Ray Naldo
 */
if (!function_exists('includeClass'))
{
    /**
     * @param string $class class name or '*' to include all classes in the directory
     * @param string $directory with trailing slash, relative to application folder (APPPATH)
     */
    function includeClass ($class, $directory = '')
    {
        if ($class == '*')
        {
            $directory = APPPATH . $directory . '*.php';
            foreach (glob($directory) as $filename)
                include_once($filename);
        }
        else
        {
            $filePath = sprintf('%s%s.php', APPPATH . $directory, $class);
            include_once($filePath);
        }
    }
}

if (!function_exists('requireClass'))
{
    /**
     * @param string $class class name or '*' to include all classes in the directory
     * @param string $directory with trailing slash, relative to application folder (APPPATH)
     */
    function requireClass ($class, $directory = '')
    {
        if ($class == '*')
        {
            $directory = APPPATH . $directory . '*.php';
            foreach (glob($directory) as $filename)
                require_once($filename);
        }
        else
        {
            $filePath = sprintf('%s%s.php', APPPATH . $directory, $class);
            require_once($filePath);
        }
    }
}

if (!function_exists('startsWith'))
{
    /**
     * @param string $string
     * @param string $prefix
     * @return bool
     */
    function startsWith ($string, $prefix)
    {
        return (strpos($string, $prefix, 0) === 0);
    }
}

if (!function_exists('endsWith'))
{
    /**
     * @param string $string
     * @param string $suffix
     * @return bool
     */
    function endsWith ($string, $suffix)
    {
        $offset = strlen($string) - strlen($suffix);
        if ($offset >= 0)
            return (strpos($string, $suffix, $offset) === $offset);
        else
            return false;
    }
}

if (!function_exists('groupObjectArray'))
{
    function groupObjectArray (array $arr, array $groupFields)
    {
        $groupedArr = array();

        $countGroup = count($groupFields);
        foreach ($arr as $row)
        {
            $group =& $groupedArr;
            for ($i = 0; $i < $countGroup; $i++)
            {
                $fieldName = $groupFields[$i];
                $fieldValue = $row->$fieldName;

                if (!isset($group[$fieldValue]))
                    $group[$fieldValue] = array();

                if ($i == ($countGroup-1))
                    $group[$fieldValue][] = $row;
                else
                    $group =& $group[$fieldValue];
            }
        }

        return $groupedArr;
    }
}

if (!function_exists('isInDateRange'))
{
    /**
     * @param string $startDateTimeStr date string
     * @param string $endDateTimeStr date string
     * @return bool true if current date (without time parts) is within range
     */
    function isInDateRange ($startDateTimeStr, $endDateTimeStr)
    {
        $now = getDateMillis();
        return ($now >= getDateMillis($startDateTimeStr) && $now <= getDateMillis($endDateTimeStr));
    }
}

if (!function_exists('isInDateTimeRange'))
{
    /**
     * @param string $startDateTimeStr date string
     * @param string $endDateTimeStr date string
     * @return bool true if current date (without time parts) is within range
     */
    function isInDateTimeRange ($startDateTimeStr, $endDateTimeStr)
    {
        $now = getDateMillis();
        return ($now >= getDateMillis($startDateTimeStr) && $now <= getDateMillis($endDateTimeStr));
    }
}

if (!function_exists('getDateMillis'))
{
    /**
     * @param string $dateTimeStr
     * @return int unix timestamp in milliseconds with time parts dropped
     */
    function getDateMillis ($dateTimeStr = null)
    {
        if (empty($dateTimeStr))
            $dateTimeStr = 'now';

        $dateTime = new DateTime($dateTimeStr);
        $dateTime->setTime(0, 0, 0);

        // getTimestamp return unix timestamp in seconds, needs to be converted to milliseconds
        return $dateTime->getTimestamp() * 1000;
    }
}

if (!function_exists('getDateTimeMillis'))
{
    /**
     * @param string $dateTimeStr date/time string
     * @return int unix timestamp in milliseconds with time parts dropped
     */
    function getDateTimeMillis ($dateTimeStr = null)
    {
        if (empty($dateTimeStr))
            $dateTimeStr = 'now';

        // don't use strtotime() to keep consistent with getDateMillis()
        $dateTime = new DateTime($dateTimeStr);

        // getTimestamp return unix timestamp in seconds, needs to be converted to milliseconds
        return $dateTime->getTimestamp()* 1000;
    }
}
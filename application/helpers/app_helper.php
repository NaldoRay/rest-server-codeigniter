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
        return (strpos($string, $suffix, $offset) === $offset);
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

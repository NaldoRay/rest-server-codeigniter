<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
Author: Ray Naldo
 */
if (!function_exists('includeClass'))
{
    /**
     * @param string $class class name
     * @param string $directory with trailing slash, relative to application folder (APPPATH)
     */
    function includeClass ($class, $directory = '')
    {
        $filePath = sprintf('%s%s.php', APPPATH.$directory, $class);
        include_once($filePath);
    }
}

if (!function_exists('requireClass'))
{
    /**
     * @param string $class class name
     * @param string $directory with trailing slash, relative to application folder (APPPATH)
     */
    function requireClass ($class, $directory = '')
    {
        $filePath = sprintf('%s%s.php', APPPATH.$directory, $class);
        require_once($filePath);
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

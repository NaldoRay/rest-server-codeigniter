<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class Autoloader
{
    private $includeDirectories;
    private $excludeDirectories;


    public function __construct ()
    {
        $this->includeDirectories  = array(
            APPPATH.'core',
            APPPATH.'models',
            APPPATH.'exceptions',
            APPPATH.'services'
        );

        $this->excludeDirectories = array();
    }

    public function init ()
    {
        spl_autoload_register('self::autoloadLibraryClass');
        spl_autoload_register(function ($class) {
            self::autoloadClass($this->includeDirectories, $class);
        });

        include_once(APPPATH.'core/helpers/core_helper.php');
    }

    /**
     * Autoload application class recursively.
     * @param array $directories
     * @param string $class
     */
    private function autoloadClass (array $directories, $class)
    {
        foreach ($directories as $directory)
        {
            if (!in_array($directory, $this->excludeDirectories))
            {
                $filePath = $directory . DIRECTORY_SEPARATOR . $class . '.php';
                if (file_exists($filePath))
                {
                    require_once ($filePath);
                    return;
                }
                else
                {
                    $subDirectories = glob($directory . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
                    self::autoloadClass($subDirectories, $class);
                }
            }
        }
    }

    /**
     * Autoload namespaced library class.
     * @param string $class
     */
    private function autoloadLibraryClass ($class)
    {
        $directory = APPPATH.'libraries';
        if (!in_array($directory, $this->excludeDirectories))
        {
            $filePath = $directory . DIRECTORY_SEPARATOR . str_replace('\\', '/', $class) . '.php';
            if (file_exists($filePath))
            {
                require_once ($filePath);
                return;
            }
        }
    }
}
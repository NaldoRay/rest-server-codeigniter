<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Overrides CI Database Loader so we could load our (custom) db driver extension.
 * https://github.com/bcit-ci/CodeIgniter/wiki/Extending-Database-Drivers
 * http://forum.codeigniter.com/thread-35992.html
 *
 * @author Modified by Ray Naldo
 */
class MY_Loader extends CI_Loader
{
    public function initialize ()
    {
        parent::initialize();

        $this->helper('file');
        include_once(APPPATH.'core/helpers/log_helper.php');
    }

    public function model ($model, $name = '', $db_conn = FALSE)
    {
        if (is_array($model))
        {
            // copied from core
            foreach ($model as $key => $value)
            {
                if (is_int($key))
                    $this->model($value, '', $db_conn);
                else
                    $this->model($key, $value, $db_conn);
            }
            return $this;
        }

        // autoload model class from subfolders if any
        class_exists($model, true);

        return parent::model($model, $name, $db_conn);
    }

    public function service ($service, $object_name = null)
    {
        $CI =& get_instance();

        if (empty($object_name))
            $object_name = strtolower($service);

        if (!isset($CI->$object_name))
            $CI->$object_name = new $service();

        return $this;
    }

    public function database ($params = '', $return = FALSE, $query_builder = NULL)
    {
        // Grab the super object
        $CI =& get_instance();

        // Do we even need to load the database class?
        if ($return === FALSE && $query_builder === NULL && isset($CI->db) && is_object($CI->db) && ! empty($CI->db->conn_id))
        {
            return FALSE;
        }

        require_once(BASEPATH.'database/DB.php');

        // Load the DB class
        $db =& DB($params, $query_builder);

        $my_driver = config_item('subclass_prefix').'DB_'.$db->dbdriver.'_driver';
        $my_driver_file = APPPATH.'core/'.$my_driver.'.php';

        if (file_exists($my_driver_file))
        {
            require_once($my_driver_file);
            $db = new $my_driver(get_object_vars($db));
        }

        if ($return === TRUE)
        {
            return $db;
        }

        // Initialize the db variable.  Needed to prevent
        // reference errors with some configurations
        $CI->db = '';
        $CI->db = $db;

        return $this;
    }
}

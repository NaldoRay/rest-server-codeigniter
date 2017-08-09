<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader
{
 	/**
     * Overrides CI Database Loader sehingga kita bisa load db driver hasil extends/custom.
	 * https://github.com/bcit-ci/CodeIgniter/wiki/Extending-Database-Drivers
	 * http://forum.codeigniter.com/thread-35992.html
     */
    public function database($params = '', $return = FALSE, $query_builder = NULL)
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

?>
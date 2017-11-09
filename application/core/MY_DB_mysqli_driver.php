<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
	Biar jalan (sukses di-override), harus pake query builder.
	Pastikan $query_builder = TRUE; di config/database.php.
*/
class MY_DB_mysqli_driver extends CI_DB_mysqli_driver
{
	/**
		Semua fungsi query dari Query Builder (pasti) panggil fungsi ini
	*/
	// Override
	public function query($sql, $binds = FALSE, $return_object = NULL)
	{
		$ret = parent::query($sql, $binds, $return_object);
		
		if (strpos(strtoupper(ltrim($sql)), 'SELECT') !== 0)
		{
			// non-select statement: INSERT, UPDATE, DELETE, etc.
			$lastQuery = $this->last_query();
			// buat query jadi one-liner, remove semua newline
			$lastQuery = preg_replace('/[ \t\r\n]+/', ' ', $lastQuery);

			$success = $ret;
			if ($success)
			{
				$error = $this->error();
                $success = (empty($error) || empty($error['code']));
			}
			
			if ($success)
				logQuery($lastQuery);
			else
				logFailedQuery($lastQuery);
		}

		return $ret;
	}
}

/**
	RN @ 2016
*/
?>
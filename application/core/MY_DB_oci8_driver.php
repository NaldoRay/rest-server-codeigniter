<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Need to use query builder for this to work.
 * Make sure $query_builder = TRUE; in config/database.php.
 *
 * @author Ray Naldo
 */
class MY_DB_oci8_driver extends CI_DB_oci8_driver
{
    public function __construct (array $params)
    {
        parent::__construct($params);
        // set default date format to ISO-8601: [YYYY]-[MM]-[DD]
        parent::query("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'");
        parent::query("ALTER SESSION SET NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS.ff6'");
    }

	public function query($sql, $binds = FALSE, $return_object = NULL)
	{
		$result = parent::query($sql, $binds, $return_object);
		
		if (strpos(strtoupper(ltrim($sql)), 'SELECT') !== 0)
		{
			// non-select statement: INSERT, UPDATE, DELETE, etc.
			$lastQuery = $this->last_query();
			// format query statement to one-liner, remove all newlines
			$lastQuery = preg_replace('/[ \t\r\n]+/', ' ', $lastQuery);

			$success = $result;
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
        else
        {
            // convert fields' value according to their type
            $fields = $result->field_data();
            $rows = $result->result();
            if (!empty($fields) && !empty($rows))
            {
                // check is row really has one of the fields
                $firstRow = reset($rows);
                $firstField = reset($fields);
                if (isset($firstRow->{$firstField->name}))
                {
                    foreach ($fields as $field)
                    {
                        switch ($field->type)
                        {
                            case 'NUMBER':
                            {
                                // convert NUMBER field's value to number
                                $fieldName = $field->name;
                                foreach ($rows as $row)
                                {
                                    $fieldValue = $row->{$fieldName};
                                    if (!is_null($fieldValue))
                                        $row->{$fieldName} = $fieldValue + 0;
                                }
                                break;
                            }
                            case 'TIMESTAMP':
                            {
                                // convert TIMESTAMP field's value to ISO-8601 date & time format
                                $fieldName = $field->name;
                                foreach ($rows as $row)
                                {
                                    $fieldValue = $row->{$fieldName};
                                    if (!is_null($fieldValue))
                                        $row->{$fieldName} = date(DateTime::ISO8601, strtotime($fieldValue));
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }

		return $result;
	}

    /**
     * Fix package name cannot be empty.
     * Based on oci8_driver.php - CI_DB_oci8_driver::stored_procedure().
     */
    public function stored_procedure($package, $procedure, array $params)
    {
        if (empty($procedure))
        {
            log_message('error', 'Invalid procedure: '.$procedure);
            return ($this->db_debug) ? $this->display_error('db_invalid_query') : FALSE;
        }
        else if (!empty($package))
        {
            $procedure = $package.'.'.$procedure;
        }

        // Build the query string
        $sql = 'BEGIN '.$procedure.'(';

        /*
         * Copied from oci8_driver.php CI_DB_oci8_driver::stored_procedure()
         */
        $have_cursor = FALSE;
        foreach ($params as $param)
        {
            $sql .= $param['name'].',';

            if (isset($param['type']) && $param['type'] === OCI_B_CURSOR)
            {
                $have_cursor = TRUE;
            }
        }
        $sql = trim($sql, ',').'); END;';

        $this->_reset_stmt_id = FALSE;
        $this->stmt_id = oci_parse($this->conn_id, $sql);
        $this->_bind_params($params);
        $result = $this->query($sql, FALSE, $have_cursor);
        $this->_reset_stmt_id = TRUE;
        return $result;
    }
	
	/**
     * Fix `field_data()` returns one-less fields (missing the last field) on subsequent queries after doing select query with `limit()`.
     * @author Ray Naldo.
     */
    protected function _reset_select ()
    {
        parent::_reset_select();
        $this->limit_used = FALSE;
    }
}

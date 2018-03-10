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
                                    $row->{$fieldName} = $row->{$fieldName} + 0;
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
     * Fix `field_data()` returns one-less fields (missing the last field) on subsequent queries after doing select query with `limit()`.
     * @author Ray Naldo.
     */
    protected function _reset_select ()
    {
        parent::_reset_select();
        $this->limit_used = FALSE;
    }
}

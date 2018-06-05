<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Need to use query builder for this to work.
 * Make sure $query_builder = TRUE; in config/database.php.
 *
 * @author Ray Naldo
 */
class MY_DB_mysqli_driver extends CI_DB_mysqli_driver
{
    /*
     * http://us2.php.net/manual/en/mysqli-result.fetch-field.php
     * https://stackoverflow.com/questions/5824722/mysqli-how-to-get-the-type-of-a-column-in-a-table
     */
    private static $TYPE_TINYINT = 1;
    private static $TYPE_SMALLINT = 2;
    private static $TYPE_MEDIUMINT = 9;
    private static $TYPE_INTEGER = 3;
    private static $TYPE_BIGINT = 8;
    private static $TYPE_SERIAL = 8;
    private static $TYPE_FLOAT = 4;
    private static $TYPE_DOUBLE = 5;
    private static $TYPE_DECIMAL = 246;
    private static $TYPE_NUMERIC = 246;
    private static $TYPE_FIXED = 246;

    private static $TYPE_DATE = 10;
    private static $TYPE_TIME = 11;
    private static $TYPE_DATETIME = 12;
    private static $TYPE_TIMESTAMP = 7;
    private static $TYPE_YEAR = 13;


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
            /** @var CI_DB_result $result */
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
                            case self::$TYPE_TINYINT:
                            case self::$TYPE_SMALLINT:
                            case self::$TYPE_MEDIUMINT:
                            case self::$TYPE_INTEGER:
                            case self::$TYPE_BIGINT:
                            case self::$TYPE_SERIAL:
                            case self::$TYPE_FLOAT:
                            case self::$TYPE_DOUBLE:
                            case self::$TYPE_DECIMAL:
                            case self::$TYPE_NUMERIC:
                            case self::$TYPE_FIXED:
                            {
                                // convert to number
                                $fieldName = $field->name;
                                foreach ($rows as $row)
                                {
                                    $fieldValue = $row->{$fieldName};
                                    if (!is_null($fieldValue))
                                        $row->{$fieldName} = $fieldValue + 0;
                                }
                                break;
                            }
                            case self::$TYPE_DATETIME:
                            case self::$TYPE_TIMESTAMP:
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
}

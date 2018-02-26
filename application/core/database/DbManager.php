<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class DbManager
{
    private $transactionLevel;
    /** @var CI_DB_query_builder[]|CI_DB_driver[]  */
    private $dbMap;
    /** @var CI_Controller  */
    private $CI;


    public function __construct ()
    {
        $this->transactionLevel = 0;
        $this->dbMap = array();
        $this->CI =& get_instance();
    }

    public function getDb ($name)
    {
        if (isset($this->dbMap[$name]))
        {
            return $this->dbMap[$name];
        }
        else
        {
            $this->dbMap[$name] = $this->CI->load->database($name, true);
            if ($this->transactionLevel > 0)
            {
                for ($i = 0; $i < $this->transactionLevel; $i++)
                    $this->dbMap[$name]->trans_begin();
            }

            return $this->dbMap[$name];
        }
    }

    public function startTransaction ()
    {
        $this->transactionLevel++;

        foreach ($this->dbMap as $db)
            $db->trans_begin();
    }

    public function endTransaction ()
    {
        if ($this->isTransactionSuccess())
        {
            $this->commitTransaction();
            return true;
        }
        else
        {
            $this->rollbackTransaction();
            return false;
        }
    }

    public function isTransactionSuccess ()
    {
        foreach ($this->dbMap as $db)
        {
            if (!$db->trans_status())
                return false;
        }
        return true;
    }

    public function commitTransaction ()
    {
        if ($this->transactionLevel > 0)
            $this->transactionLevel--;

        foreach ($this->dbMap as $db)
            $db->trans_commit();
    }

    public function rollbackTransaction ()
    {
        if ($this->transactionLevel > 0)
            $this->transactionLevel--;

        foreach ($this->dbMap as $db)
            $db->trans_rollback();
    }
}
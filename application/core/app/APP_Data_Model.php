<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
abstract class APP_Data_Model extends APP_Model
{
    /** @var  CI_DB_query_builder|CI_DB_driver */
    private static $anyDb;


    protected function getAnyDb ()
    {
        if (is_null(self::$anyDb))
            self::$anyDb = $this->load->database('any', true);

        return self::$anyDb;
    }

    protected function loadData ($entity)
    {
        if (!isset($this->externalData))
            $this->load->model(External_data::class, 'externalData');

        $this->externalData->rightJoin($entity, ['extraData1', 'extraData2']);
    }
}
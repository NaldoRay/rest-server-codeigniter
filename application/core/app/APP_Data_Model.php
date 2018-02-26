<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
abstract class APP_Data_Model extends APP_Model
{
    protected function getAnyDb ()
    {
        return $this->getDb('any');
    }

    protected function loadData ($entity)
    {
        if (!isset($this->externalData))
            $this->load->model(Join_data::class, 'externalData');

        $this->externalData->rightJoin($entity, ['extraData1', 'extraData2']);
    }
}
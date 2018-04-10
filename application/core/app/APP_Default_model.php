<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
abstract class APP_Default_model extends APP_Model
{
    public function __construct ()
    {
        parent::__construct('default');
    }

    // example: helper to load external data
    protected function loadData ($entity)
    {
        if (!isset($this->externalData))
            $this->load->model(External_data::class, 'externalData');

        $entity->externalData = $this->externalData->get($entity->externalId);
    }
}
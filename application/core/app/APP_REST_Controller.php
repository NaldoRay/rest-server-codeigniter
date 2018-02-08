<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
class APP_REST_Controller extends MY_REST_Controller
{
    public function __construct ()
    {
        parent::__construct();
        // override language
        $this->setLanguage(self::$LANG_INDONESIA);
        $this->load->library(File_manager::class, null, 'fileManager');
    }

    protected function processData (array $data)
    {
        $data = parent::processData($data);

        // auto-add 'API-User' header value as 'inupby' field to data, if header exists.
        $inupby = $this->input->get_request_header('API-User');
        if (!is_null($inupby))
        {
            if ($this->isSequential($data))
            {
                foreach ($data as $key => $row)
                {
                    if (is_array($row) && !$this->isSequential($row))
                    {
                        $data[$key]['inupby'] = $inupby;
                    }
                }
            }
            else
            {
                $data['inupby'] = $inupby;
            }
        }

        return $data;
    }

    private function isSequential (array $arr)
    {
        foreach ($arr as $key => $value)
        {
            if (is_string($key))
                return false;
        }
        return true;
    }
}
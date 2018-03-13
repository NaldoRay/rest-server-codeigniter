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

        // fix "Fatal error: Class 'CI_Model' not found"
        require_once(BASEPATH.'core/Model.php');

        // use 'API-User' header value as default 'inupby' value for each insert/update on app model
        $inupby = $this->input->get_request_header('API-User');
        APP_Model::setDefaultInupby($inupby);

        // default to Bahasa Indonesia if language header is not exists
        $language = $this->input->get_request_header('Accept-Language');
        if (is_null($language))
            $this->setLanguage(self::$LANG_INDONESIA);

        $this->load->library(File_manager::class, null, 'fileManager');
    }
}
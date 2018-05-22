<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
class APP_REST_Controller extends MY_REST_Controller
{
    private static $LANG_INDONESIA = 'indonesia';

    public function __construct ()
    {
        parent::__construct();

        // fix "Fatal error: Class 'CI_Model' not found"
        require_once(BASEPATH.'core/Model.php');

        // use 'API-User' header value as default 'inupby' value for each insert/update on app model
        $inupby = $this->input->get_request_header('API-User');
        APP_Model::setDefaultInupby($inupby);

        $this->load->library(File_manager::class, null, 'fileManager');
    }

    protected function getLanguage ()
    {
        // default to Bahasa Indonesia if language header is not exists
        $language = $this->input->get_request_header('Accept-Language');
        if (empty($language) || $language == 'id')
            return self::$LANG_INDONESIA;
        else
            return self::$LANG_ENGLISH;
    }
}
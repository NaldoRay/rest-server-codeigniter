<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
class APP_REST_Controller extends MY_REST_Controller
{
    private static $LANG_INDONESIA = 'indonesia';

    private $auth;
    private $shouldCheckAcl;


    public function __construct ()
    {
        parent::__construct();

        // fix "Fatal error: Class 'CI_Model' not found"
        require_once(BASEPATH.'core/Model.php');

        if ($this->shouldCheckAcl())
        {
            // use 'API-User' header value as default 'inupby' value for each insert/update on app model
            $authHeader = $this->input->get_request_header('Authorization');
            $authParams = explode(' ', $authHeader);
            if (count($authParams) != 2 || $authParams[0] != 'Bearer' || empty($authParams[1]))
                $this->respondForbidden('Forbidden Access', 'Auth');

            $token = $authParams[1];
            $tokenParts = explode(':', base64_decode($token));
            if (count($tokenParts) != 3)
                $this->respondForbidden('Forbidden Access', 'Auth');

            $controller = $tokenParts[0];
            $function = $tokenParts[1];
            $accessToken = $tokenParts[2];

            $this->load->service(Acl_service::class, 'aclServiceModel');
            /** @var Acl_service $aclServiceModel */
            $aclServiceModel = $this->aclServiceModel;
            $authResponse = $aclServiceModel->getUserAuth($controller, $function, $accessToken);
            if ($authResponse->success)
            {
                $this->auth = $authResponse->data;
                APP_Model::setDefaultInupby($this->auth->username);
            }
            else
            {
                $this->forwardResponse($authResponse);
                exit;
            }
        }

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

    protected function hasProdi ($kodeProdi)
    {
        return in_array($kodeProdi, $this->getAllKodeProdi());
    }

    protected function getAllKodeProdi ()
    {
        return isset($this->auth) ? $this->auth->prodiArr : array();
    }

    protected function hasUnit ($kodeUnit)
    {
        return in_array($kodeUnit, $this->getAllKodeUnit());
    }

    protected function getAllKodeUnit ()
    {
        return isset($this->auth) ? $this->auth->unitArr : array();
    }

    protected function shouldCheckAcl ()
    {
        if (!isset($this->shouldCheckAcl))
        {
            /*
             * Cek acl hanya jika bukan ip developer dan bukan ip ws
             */
            $ipAddress = $this->input->ip_address();

            // jika bukan IP komputer BTI
            if (strpos($ipAddress, '10.1.11') === 0)
            {
                $this->shouldCheckAcl = false;
            }
            else
            {
                $this->shouldCheckAcl = true;
            }
        }

        return $this->shouldCheckAcl;
    }
}
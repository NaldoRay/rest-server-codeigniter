<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 * @property File_manager $fileManager
 */
class APP_REST_Controller extends MY_REST_Controller
{
    private static $LANG_INDONESIA = 'indonesia';

    private $username;
    private $groupMap;


    public function __construct ()
    {
        parent::__construct();

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

    protected function authorizeClient ()
    {

        // use 'API-User' header value as default 'inupby' value for each insert/update on app model
        $authHeader = $this->input->get_request_header('Authorization');
        $authParams = explode(' ', $authHeader);
        if (count($authParams) != 2 || $authParams[0] != 'Bearer' || empty($authParams[1]))
        {
            $this->respondForbidden('Forbidden Access', 'API');
            exit;
        }

        $token = $authParams[1];
        $tokenParts = explode(':', base64_decode($token));
        if (count($tokenParts) != 3)
        {
            $this->respondForbidden('Forbidden Access', 'API');
            exit;
        }

        $controller = $tokenParts[0];
        $function = $tokenParts[1];
        $accessToken = $tokenParts[2];

        $this->load->service(Acl_service::class, 'aclServiceModel');
        /** @var Acl_service $aclServiceModel */
        $aclServiceModel = $this->aclServiceModel;
        $authResponse = $aclServiceModel->getUserAuth($controller, $function, $accessToken);
        if ($authResponse->success)
        {
            $auth = $authResponse->data;

            $this->username = $auth->username;
            $this->groupMap = array();
            foreach ($auth->groups as $group)
            {
                $groupId = $group->groupId;

                $this->groupMap[$groupId]['unitArr'] = $group->unitArr;
                $this->groupMap[$groupId]['prodiArr'] = $group->prodiArr;
            }

            APP_Model::setInupby($this->getUsername());
        }
        else
        {
            $this->forwardResponse($authResponse);
            exit;
        }
    }

    protected function getUsername ()
    {
        return isset($this->username) ? $this->username : null;
    }

    protected function validateAclProdiQuery (QueryParam $param)
    {
        if ($this->shouldValidateAcl())
        {
            $prodiArr = $this->getAllKodeProdi();
            if (empty($prodiArr))
            {
                $this->respondSuccess([]);
                exit;
            }
            else
            {
                $param->search(new EqualsCondition('kodeProdi', $prodiArr));
            }
        }
    }

    protected function validateAclProdiQueryByProdi ($kodeProdi)
    {
        try
        {
            $this->validateAclProdi($kodeProdi);
        }
        catch (ResourceNotFoundException $e)
        {
            $this->respondSuccess([]);
            exit;
        }
    }

    protected function validateAclProdi ($kodeProdi, $resource = 'Resource', $domain = 'API')
    {
        if ($this->shouldValidateAcl() && !$this->hasProdi($kodeProdi))
        {
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $resource), $domain);
        }
    }

    protected function validateAclGroup ($groupId, $resource = 'Resource', $domain = 'API')
    {
        if ($this->shouldValidateAcl() && !$this->hasGroup($groupId))
        {
            throw new ResourceNotFoundException(sprintf('%s tidak ditemukan', $resource), $domain);
        }
    }

    protected function shouldValidateAcl ()
    {
        return $this->shouldAuthorizeClient();
    }

    protected function hasProdi ($kodeProdi, $groupId = null)
    {
        return in_array($kodeProdi, $this->getAllKodeProdi($groupId));
    }

    protected function getAllKodeProdi ($groupId = null)
    {
        if (isset($this->groupMap))
        {
            if (is_null($groupId))
            {
                $kodeProdiMap = array();
                foreach ($this->groupMap as $groupData)
                {
                    foreach ($groupData['prodiArr'] as $kodeProdi)
                        $kodeProdiMap[$kodeProdi] = true;
                }
                return array_keys($kodeProdiMap);
            }
            else if (isset($this->groupMap[$groupId]))
            {
                return $this->groupMap[$groupId]['prodiArr'];
            }
        }

        return array();
    }

    protected function hasUnit ($kodeUnit, $groupId = null)
    {
        return in_array($kodeUnit, $this->getAllKodeUnit($groupId));
    }

    protected function getAllKodeUnit ($groupId = null)
    {
        if (isset($this->groupMap))
        {
            if (is_null($groupId))
            {
                $kodeUnitMap = array();
                foreach ($this->groupMap as $groupData)
                {
                    foreach ($groupData['unitArr'] as $kodeUnit)
                        $kodeUnitMap[$kodeUnit] = true;
                }
                return array_keys($kodeUnitMap);
            }
            else if (isset($this->groupMap[$groupId]))
            {
                return $this->groupMap[$groupId]['unitArr'];
            }
        }

        return array();
    }

    protected function hasGroup ($groupId)
    {
        return in_array($groupId, $this->getAllGroupId());
    }

    protected function getFirstGroupId ()
    {
        $groupIdArr = $this->getAllGroupId();
        if (empty($groupIdArr))
            return null;
        else
            return $groupIdArr[0];
    }

    protected function getAllGroupId ()
    {
        if (isset($this->groupMap))
            return array_keys($this->groupMap);
        else
            return array();
    }
}
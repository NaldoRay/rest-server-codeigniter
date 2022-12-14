<?php

/**
 * @author Ray Naldo
 */
class RestAccess
{
    /** @var CI_Controller */
    private $CI;
    /** @var bool */
    private $shouldAuthorizeClient;


    public function __construct ()
    {
        $this->CI = get_instance();

        $accessConfigExists = $this->CI->config->load('rest_access', false, true);
        if (!$accessConfigExists)
            throw new AuthorizationException('Forbidden Access');
    }

    public function check ()
    {
        $uri = $this->CI->uri->uri_string();
        $method = $this->CI->input->method(true);

        $accessArr = $this->getAllClientAccess();
        foreach ($accessArr as $access)
        {
            if ($this->isUriAllowed($uri, $access) && $this->isMethodAllowed($method, $access))
                return;
        }

        throw new AuthorizationException('Forbidden Access');
    }

    private function getAllClientAccess ()
    {
        $clientIpAddress = $this->getClientIpAddress();

        $clientAccessArr = $this->CI->config->item('rest_client_access');
        foreach ($clientAccessArr as $ipAddress => $accessArr)
        {
            if ($ipAddress == $clientIpAddress)
            {
                return $accessArr;
            }
        }

        // return default/other config if any
        if (isset($clientAccessArr[ REST_OTHER_IP_ADDRESSES ]))
        {
            return $clientAccessArr[ REST_OTHER_IP_ADDRESSES ];
        }
        else
        {
            return [];
        }
    }

    private function isUriAllowed ($uri, array $access)
    {
        foreach ($access['uris'] as $uriPattern)
        {
            $pattern = sprintf('#%s#', $uriPattern);
            if (preg_match($pattern, $uri))
                return true;
        }

        return false;
    }

    private function isMethodAllowed ($method, array $access)
    {
        foreach ($access['methods'] as $methodPattern)
        {
            $pattern = sprintf('#%s#', $methodPattern);
            if (preg_match($pattern, $method))
                return true;
        }

        return false;
    }

    public function shouldAuthorizeClient ()
    {
        if (!isset($this->shouldAuthorizeClient))
        {
            $authClients = $this->CI->config->item('rest_auth_clients');
            if (empty($authClients))
            {
                $this->shouldAuthorizeClient = false;
            }
            else
            {
                $clientIpAddress = $this->getClientIpAddress();
                $this->shouldAuthorizeClient = in_array($clientIpAddress, $authClients);
            }
        }

        return $this->shouldAuthorizeClient;
    }

    private function getClientIpAddress ()
    {
        return $this->CI->input->ip_address();
    }
}
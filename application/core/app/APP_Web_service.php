<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * @author Ray Naldo
 */
class APP_Web_service extends MY_Web_service
{
    private $fileViewer;


    public function __construct ($baseUrl)
    {
        parent::__construct($baseUrl);

        $this->fileViewer = new FileViewer();
    }

    public function get ($uri = '', GetParam $param = null)
    {
        $this->setRequestHeaders();

        return parent::get($uri, $param);
    }

    public function post ($uri = '', array $params = null)
    {
        $this->setRequestHeaders();

        return parent::post($uri, $params);
    }

    public function put ($uri = '', array $params = null)
    {
        $this->setRequestHeaders();

        return parent::put($uri, $params);
    }

    public function patch ($uri = '', array $params = null)
    {
        $this->setRequestHeaders();

        return parent::patch($uri, $params);
    }

    public function delete ($uri = '', array $params = null)
    {
        $this->setRequestHeaders();

        return parent::delete($uri, $params);
    }

    public function viewFile ($uri = '', $renamedFilename = null, array $headers = null, $caFilePath = null)
    {
        $this->fileViewer->viewRemoteFile($this->getEndpointUrl($uri), $renamedFilename, $headers, $caFilePath);
    }

    public function downloadFile ($uri = '', $renamedFilename = null, array $headers = null, $caFilePath = null)
    {
        $this->fileViewer->downloadRemoteFile($this->getEndpointUrl($uri), $renamedFilename, $headers, $caFilePath);
    }

    public function readFile ($uri = '', array $headers = null, $caFilePath = null)
    {
        return $this->fileViewer->readRemoteFile($this->getEndpointUrl($uri), $headers, $caFilePath);
    }

    private function setRequestHeaders ()
    {
        $CI =& get_instance();

        $clientIP = $CI->input->get_request_header('X-Client-IP');
        if (is_null($clientIP))
            $clientIP = $CI->input->ip_address();

        $this->setHeader('X-Client-IP', $clientIP);
    }
}
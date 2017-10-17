<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('viewer/FileViewer.php');

/**
 * Generate view/download link for (local) files
 * Author: Ray N
 * Date: 10/12/2017
 * Time: 13:07
 */
class File_provider
{
    private $viewUri = 'files/';
    private $sourceDir = '/data/files/';
    private $tempDir = '/data/tmp/';

    private $fileViewer;


    public function __construct ($config = array())
    {
        $this->fileViewer = new FileViewer();

        foreach ($config as $key => $value)
        {
            if (isset($this->$key))
                $this->$key = $value;
        }
    }

    public function getViewUrl ($filename)
    {
        return $this->createAccessUrl($filename, $this->viewUri);
    }

    private function createAccessUrl ($sourceFilename, $accessUri)
    {
        $CI =& get_instance();
        $CI->load->helper('url');

        $tempFilename = sha1($sourceFilename);
        copy($this->sourceDir.$sourceFilename, $this->tempDir.$tempFilename);

        return site_url(sprintf('%s%s', $accessUri, $tempFilename));
    }

    public function viewFile ($filename)
    {
        $filePath = sprintf('%s%s', $this->tempDir, $filename);

        $this->fileViewer->viewFile($filePath);
        unlink($filePath);
    }
}
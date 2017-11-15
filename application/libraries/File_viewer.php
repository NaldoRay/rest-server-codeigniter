<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('viewer/FileViewer.php');

/**
 * @author Ray Naldo
 */
class File_viewer
{
    private $fileViewer;

    public function __construct ()
    {
        $this->fileViewer = new FileViewer();
    }

    public function viewFile ($filePath, $renamedFilename = null)
    {
        $shown = $this->fileViewer->viewFile($filePath, $renamedFilename);
        if (!$shown)
            show_404();
    }

    public function viewRemoteFile ($fileUrl, $renamedFilename = null)
    {
        $shown = $this->fileViewer->viewRemoteFile($fileUrl, $renamedFilename);
        if (!$shown)
            show_404();
    }

    public function downloadFile ($filePath, $renamedFilename)
    {
        $shown = $this->fileViewer->downloadFile($filePath, $renamedFilename);
        if (!$shown)
            show_404();
    }

    public function downloadRemoteFile ($fileUrl, $renamedFilename)
    {
        $shown = $this->fileViewer->downloadRemoteFile($fileUrl, $renamedFilename);
        if (!$shown)
            show_404();
    }
}
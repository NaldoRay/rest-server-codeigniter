<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('viewer/FileViewer.php');
require_once('System.php');

/**
 * @author Ray Naldo
 */
class File_manager
{
    private $fileViewer;
    private $uploadPath;

    public function __construct (array $config = array())
    {
        $this->fileViewer = new FileViewer();

        if (isset($config['file_upload_path']))
            $this->uploadPath = $config['file_upload_path'];
        else
            $this->uploadPath = FCPATH.'upload/';
    }

    /**
     * Needs Imagick extension.
     * @param string $filePath
     * @param string|null $renamedFilename
     */
    public function viewFileAsPdf ($filePath, $renamedFilename = null)
    {
        $mime = mime_content_type($filePath);
        if ($mime == 'application/pdf')
        {
            $this->viewFile($filePath, $renamedFilename);
        }
        else
        {
            if (is_null($renamedFilename))
                $pdfFilename = 'File.pdf';
            else
                $pdfFilename = $renamedFilename;

            $tmpPath = System::mktemp('-d upload');
            $pdfFilePath = sprintf('%s/%s', $tmpPath, $pdfFilename);

            $imagick = new Imagick($filePath);
            $imagick->setImageFormat('pdf');
            $imagick->writeImage($pdfFilePath);

            $this->fileViewer->viewFile($pdfFilePath, $pdfFilename);
        }
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
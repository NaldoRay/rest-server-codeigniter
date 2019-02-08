<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('System.php');

/**
 * @author Ray Naldo
 */
class File_manager
{
    private $fileViewer;
    private $baseFilePath;


    public function __construct (array $config = array())
    {
        $this->fileViewer = new FileViewer();

        if (isset($config['file_base_path']))
            $this->baseFilePath = $config['file_base_path'];
        else
            $this->baseFilePath = FCPATH.'files/';
    }

    /**
     * Path must include a trailing slash if you want to use absolute/full path or null.
     * @param string|null $baseFilePath
     */
    public function setBaseFilePath ($baseFilePath)
    {
        $this->baseFilePath = $baseFilePath;
    }

    public function viewImage ($imagePath, $imageNotFoundPath = null)
    {
        $imagePath = $this->getFullPath($imagePath);
        $imageNotFoundPath = $this->getFullPath($imageNotFoundPath);

        $shown = $this->fileViewer->viewImage($imagePath, $imageNotFoundPath);
        if (!$shown)
            show_404();
    }

    public function viewRemoteImage ($imageUrl, $imageNotFoundPath = null, array $headers = null, $caFilePath = null)
    {
        $imageNotFoundPath = $this->getFullPath($imageNotFoundPath);

        $shown = $this->fileViewer->viewRemoteImage($imageUrl, $imageNotFoundPath, $headers, $caFilePath);
        if (!$shown)
            show_404();
    }

    /**
     * Needs Imagick extension.
     * @param string $filePath relative to base file path, or full path if base path is null
     * @param null $renamedFilename
     */
    public function viewFileAsPdf ($filePath, $renamedFilename = null)
    {
        $filePath = $this->getFullPath($filePath);

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

            $tmpPath = System::mktemp('-d file');
            $pdfFilePath = sprintf('%s/%s', $tmpPath, $pdfFilename);

            $imagick = new Imagick($filePath);
            $imagick->setImageFormat('pdf');
            $imagick->writeImage($pdfFilePath);

            $this->fileViewer->viewFile($pdfFilePath, $pdfFilename);
        }
    }

    public function viewFile ($filePath, $renamedFilename = null)
    {
        $filePath = $this->getFullPath($filePath);

        $shown = $this->fileViewer->viewFile($filePath, $renamedFilename);
        if (!$shown)
            show_404();
    }

    public function viewRemoteFile ($fileUrl, $renamedFilename = null, array $headers = null, $caFilePath = null)
    {
        $shown = $this->fileViewer->viewRemoteFile($fileUrl, $renamedFilename, $headers, $caFilePath);
        if (!$shown)
            show_404();
    }

    public function downloadFile ($filePath, $renamedFilename)
    {
        $filePath = $this->getFullPath($filePath);
        $shown = $this->fileViewer->downloadFile($filePath, $renamedFilename);
        if (!$shown)
            show_404();
    }

    public function downloadRemoteFile ($fileUrl, $renamedFilename, array $headers = null, $caFilePath = null)
    {
        $shown = $this->fileViewer->downloadRemoteFile($fileUrl, $renamedFilename, $headers, $caFilePath);
        if (!$shown)
            show_404();
    }

    public function readRemoteFile ($fileUrl, array $headers = null, $caFilePath = null)
    {
        return $this->fileViewer->readRemoteFile($fileUrl, $headers, $caFilePath);
    }

    public function uploadFile ($field, $filePath)
    {
        if ($this->uploadFileExists($field) && !empty($filePath))
        {
            $sourcePath = $_FILES[ $field ]["tmp_name"];
            $destPath = $this->getFullPath($filePath);

            $destDir = dirname($destPath);
            if (!is_dir($destDir))
                mkdir($destDir, 0775, true);

            return copy($sourcePath, $destPath);
        }
        return false;
    }

    public function uploadFileExists ($field)
    {
        return isset($_FILES[ $field ]);
    }

    private function getFullPath ($filePath)
    {
        return sprintf('%s%s', $this->baseFilePath, $filePath);
    }
}
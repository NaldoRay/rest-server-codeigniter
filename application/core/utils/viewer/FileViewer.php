<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class FileViewer
{
    private $tmpFile;

    public function viewImage ($imagePath, $imageNotFoundPath = null)
    {
        $shown = $this->view($imagePath);
        if ($shown)
        {
            return true;
        }
        else
        {
            if (is_null($imageNotFoundPath))
                return false;
            else
                return $this->viewFile($imageNotFoundPath);
        }
    }

    public function viewRemoteImage ($imageUrl, $imageNotFoundPath = null, array $headers = null, $caFilePath = null)
    {
        $tmpFilePath = $this->createTemporaryRemoteFile($imageUrl, $headers, $caFilePath);
        $shown = $this->view($tmpFilePath);
        if ($shown)
        {
            return true;
        }
        else
        {
            if (is_null($imageNotFoundPath))
                return false;
            else
                return $this->viewFile($imageNotFoundPath);
        }
    }

    public function viewFile ($filePath, $renamedFilename = null)
    {
        return $this->view($filePath, $renamedFilename);
    }

    public function viewRemoteFile ($fileUrl, $renamedFilename = null, array $headers = null, $caFilePath = null)
    {
        $tmpFilePath = $this->createTemporaryRemoteFile($fileUrl, $headers, $caFilePath);
        return $this->view($tmpFilePath, $renamedFilename);
    }

    public function downloadFile ($filePath, $renamedFilename = null)
    {
        return $this->download($filePath, $renamedFilename);
    }

    public function downloadRemoteFile ($fileUrl, $renamedFilename = null, array $headers = null, $caFilePath = null)
    {
        $tmpFilePath = $this->createTemporaryRemoteFile($fileUrl, $headers, $caFilePath);
        return $this->download($tmpFilePath, $renamedFilename);
    }

    private function createTemporaryRemoteFile ($fileUrl, array $headers = null, $caFilePath = null)
    {
        if (empty($caFilePath))
        {
            $contextOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ];
        }
        else
        {
            $contextOptions = [
                'ssl' => [
                    'verify_peer' => true,
                    'cafile' => $caFilePath
                ]
            ];
        }

        if (!empty($headers))
        {
            $httpHeaders = array();
            foreach ($headers as $key => $value)
                $httpHeaders[] = sprintf('%s: %s', $key, $value);

            $contextOptions['http'] = [
                'method' => 'GET',
                'header' => implode('\\r\\n', $httpHeaders)
            ];
        }

        $content = file_get_contents($fileUrl, null, stream_context_create($contextOptions));
        if ($content === false)
            return null;

        // automatically deleted when the script ends
        $this->tmpFile = tmpfile();
        fwrite($this->tmpFile, $content);

        $metaDatas = stream_get_meta_data($this->tmpFile);
        return $metaDatas['uri'];
    }

    private function view ($filePath, $renamedFilename = null)
    {
        if (is_file($filePath) && file_exists ($filePath))
        {
            $info = new finfo(FILEINFO_MIME_TYPE);
            $contentType = $info->file($filePath);
            $fileSize = filesize($filePath);

            if (empty($renamedFilename))
                header('Content-Disposition: inline');
            else
                header('Content-Disposition: inline; filename="'.$renamedFilename.'"');

            header('Content-Type: ' . $contentType);
            header('Content-Length: ' . $fileSize);
            // jangan di-cache
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Expires: 0');

            //Baca file dan kirim ke client.
            $ret = readfile($filePath);

            return ($ret !== false);
        }
        return false;
    }

    private function download ($filePath, $renamedFilename)
    {
        if (is_file($filePath) && file_exists ($filePath))
        {
            $info = new finfo(FILEINFO_MIME_TYPE);
            $contentType = $info->file($filePath);
            $fileSize = filesize($filePath);

            if (empty($renamedFilename))
                header('Content-Disposition: attachment');
            else
                header('Content-Disposition: attachment; filename="' . $renamedFilename . '"');

            header('Content-Type: ' . $contentType);
            header('Content-Length: ' . $fileSize);
            // ga perlu di-cache
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Expires: 0');

            // matiin output buffering buat ngehindarin masalah memory pas download file besar
            //ob_end_clean();

            $result = readfile($filePath);

            return ($result !== false);
        }
        return false;
    }

    public function readRemoteFile ($fileUrl, array $headers = null, $caFilePath = null)
    {
        if (empty($caFilePath))
        {
            $contextOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false
                ]
            ];
        }
        else
        {
            $contextOptions = [
                'ssl' => [
                    'verify_peer' => true,
                    'cafile' => $caFilePath
                ]
            ];
        }

        if (!empty($headers))
        {
            $httpHeaders = array();
            foreach ($headers as $key => $value)
                $httpHeaders[] = sprintf('%s: %s', $key, $value);

            $contextOptions['http'] = [
                'method' => 'GET',
                'header' => implode('\\r\\n', $httpHeaders)
            ];
        }

        $content = @file_get_contents($fileUrl, null, stream_context_create($contextOptions));
        if ($content === false)
            return null;
        else
            return $content;
    }
}
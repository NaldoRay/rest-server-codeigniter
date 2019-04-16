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
        $shown = $this->viewRemoteFile($imageUrl, null, $headers, $caFilePath);
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
        $response = $this->getResponse($fileUrl, $headers, $caFilePath);
        if (is_null($response))
            return false;

        if (empty($renamedFilename))
            header('Content-Disposition: inline');
        else
            header('Content-Disposition: inline; filename="'.$renamedFilename.'"');

        header('Content-Type: ' . $response['headerMap']['Content-Type']);
        header('Content-Length: ' . $response['headerMap']['Content-Length']);
        // jangan di-cache
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Expires: 0');

        echo $response['content'];

        return true;
    }

    public function downloadFile ($filePath, $renamedFilename = null)
    {
        return $this->download($filePath, $renamedFilename);
    }

    public function downloadRemoteFile ($fileUrl, $renamedFilename = null, array $headers = null, $caFilePath = null)
    {
        $response = $this->getResponse($fileUrl, $headers, $caFilePath);
        if (is_null($response))
            return false;

        if (empty($renamedFilename))
            header('Content-Disposition: attachment');
        else
            header('Content-Disposition: attachment; filename="' . $renamedFilename . '"');

        header('Content-Type: ' . $response['headerMap']['Content-Type']);
        header('Content-Length: ' . $response['headerMap']['Content-Length']);
        // ga perlu di-cache
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Expires: 0');

        echo $response['content'];

        return true;
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

            // read and send file content to client
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
        $response = $this->getResponse($fileUrl, $headers, $caFilePath);
        if (is_null($response))
            return null;
        else
            return $response['content'];
    }

    private function getResponse ($url, array $headers = null, $caFilePath = null)
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

        $content = @file_get_contents($url, null, stream_context_create($contextOptions));
        if ($content === false)
            return null;

        $headerMap = array();
        foreach ($http_response_header as $headerStr)
        {
            $headerPair = explode(':', $headerStr, 2);
            if (isset($headerPair[1]))
                $headerMap[ trim($headerPair[0]) ] = trim($headerPair[1]);
        }

        return [
            'headerMap' => $headerMap,
            'content' => $content
        ];
    }
}
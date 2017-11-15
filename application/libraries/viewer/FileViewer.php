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

    public function viewRemoteImage ($imageUrl, $imageNotFoundPath = null)
    {
        $tmpFilePath = $this->createTemporaryRemoteFile($imageUrl);
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

    public function viewRemoteFile ($fileUrl, $renamedFilename = null)
    {
        $tmpFilePath = $this->createTemporaryRemoteFile($fileUrl);
        return $this->view($tmpFilePath, $renamedFilename);
    }

    public function downloadFile ($filePath, $renamedFilename = null)
	{
		return $this->download($filePath, $renamedFilename);
	}

    public function downloadRemoteFile ($fileUrl, $renamedFilename = null)
    {
        $tmpFilePath = $this->createTemporaryRemoteFile($fileUrl);
        return $this->download($tmpFilePath, $renamedFilename);
    }

    private function createTemporaryRemoteFile ($fileUrl)
    {
        $content = file_get_contents($fileUrl);
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

            if (!empty($renamedFilename))
                header('Content-Disposition: inline; filename="'.$renamedFilename.'"');

            header('Content-type: ' . $contentType);
            header('Content-length: ' . $fileSize);
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

            header('Content-Type: ' . $contentType);
            header('Content-Disposition: attachment; filename="' . $renamedFilename . '"');
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
}
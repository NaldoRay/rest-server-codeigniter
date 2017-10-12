<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FileViewer
{
	public function viewImage ($imagePath, $imageNotFoundPath = null)
	{
		$shown = $this->viewLocal($imagePath);
		if (!$shown && !is_null($imageNotFoundPath))
		{
			$this->viewLocal($imageNotFoundPath);
		}
		else
		{
			show_404();
		}
	}

    public function viewRemoteImage ($imageUrl, $imageNotFoundPath = null)
    {
        $shown = $this->view($imageUrl);
        if (!$shown && !is_null($imageNotFoundPath))
        {
            $this->viewLocal($imageNotFoundPath);
        }
        else
        {
            show_404();
        }
    }

    public function viewFile ($filePath)
	{
		$shown = $this->viewLocal($filePath);
		if (!$shown)
		{
			show_404();
		}
	}

    public function viewRemoteFile ($fileUrl)
    {
        $shown = $this->view($fileUrl);
        if (!$shown)
        {
            show_404();
        }
    }

    public function downloadFile ($filePath, $renamedFilename = null)
	{
		$shown = $this->downloadLocal($filePath, $renamedFilename);
		if (!$shown)
		{
			show_404();
		}
	}

    public function downloadRemoteFile ($fileUrl, $renamedFilename = null)
    {
        $shown = $this->download($fileUrl, $renamedFilename);
        if (!$shown)
        {
            show_404();
        }
    }

    private function viewLocal ($filePath)
	{		
		if (is_file($filePath) && file_exists ($filePath))
		{
			return $this->view($filePath);
		}
		
		return FALSE;
	}

    private function downloadLocal ($filePath, $renamedFilename)
	{
		if (is_file($filePath) && file_exists ($filePath))
		{
			return $this->download($filePath, $renamedFilename);
		}

		return FALSE;
	}

    private function view ($filePath)
    {
        $info = new finfo(FILEINFO_MIME_TYPE);
        $contentType = $info->file($filePath);
        $fileSize = filesize($filePath);

        header('Content-type: ' . $contentType);
        header('Content-length: ' . $fileSize);
        // jangan di-cache
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Expires: 0');

        //Baca file dan kirim ke client.
        $ret = readfile($filePath);

        return ($ret !== false);
    }

    private function download ($filePath, $renamedFilename)
    {
        $info = new finfo(FILEINFO_MIME_TYPE);
        $contentType = $info->file($filePath);
        $fileSize = filesize($filePath);

        header('Content-Type: '.$contentType);
        header('Content-Disposition: attachment; filename="'.$renamedFilename.'"');
        header('Content-Length: '.$fileSize);
        // ga perlu di-cache
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Expires: 0');

        // matiin output buffering buat ngehindarin masalah memory pas download file besar
        //ob_end_clean();

        $result = readfile($filePath);

        return ($result !== false);
    }
}
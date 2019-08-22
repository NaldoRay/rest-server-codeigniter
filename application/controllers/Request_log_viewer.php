<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author  Ray Naldo
 */
class Request_log_viewer extends APP_REST_Controller
{
    public function view_get ()
    {
        $logs = array();
        $id = 0;

        $files = $this->getFiles();
        foreach ($files as $file)
        {
            $fileName = $file->getFilename();
            if (preg_match('/_([0-9]{4})([0-9]{2})?(?:([0-9]{2})|-(week[0-9]+))?\\.txt/', $fileName, $matches))
            {
                $logName = $matches[1];
                if (isset($matches[2]))
                {
                    $logName .= '-' . $matches[2];
                    if (isset($matches[3]))
                        $logName .= '-' . $matches[3];
                }
            }
            else
            {
                $logName = $fileName;
            }

            $logs[] = (object) [
                'id' => $id++,
                'name' => $logName
            ];
        }

        $data = [
            'logs' => $logs
        ];
        $this->load->view('request_log_viewer', $data);
    }

    /**
     * @param $requestIdx
     */
    public function requests_get ($requestIdx)
    {
        $requests = array();

        $files = $this->getFiles();
        if (isset($files[$requestIdx]))
        {
            $file = $files[$requestIdx];
            $filePath = $file->getPathname();

            $search = $this->get('search');
            if (is_null($search ))
            {
                if ($file = fopen($filePath, 'r'))
                {
                    while (($line = fgets($file)) !== false)
                    {
                        $request = $this->parseLog($line);
                        if (!is_null($request))
                            $requests[] = json_decode($line);
                    }

                    fclose($file);
                }
            }
            else
            {
                $command = sprintf('cat "%s" | grep -i "%s" 2>&1',
                    addcslashes($filePath, '"'),
                    addcslashes($search, '"')
                );
                $output = shell_exec($command);

                $separator = PHP_EOL;
                $line = strtok($output, $separator);
                while ($line !== false)
                {
                    $request = $this->parseLog($line);
                    if (!is_null($request))
                        $requests[] = $request;

                    $line = strtok($separator);
                }
            }
        }
        // display last row (latest log) first
        $requests = array_reverse($requests);
        $this->respondSuccess($requests);
    }

    /**
     * @return array
     */
    private function getFiles ()
    {
        $files = array();

        $logPath = $this->config->item('app_context_error_log_path');
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($logPath));
        foreach ($iterator as $file)
        {
            if (!$file->isDir())
                $files[] = $file;
        }
        // sort desc by file name
        usort($files, function ($a, $b)
        {
            if ($a->getFilename() > $b->getFilename())
                return -1;
            else if ($a->getFilename() < $b->getFilename())
                return 1;
            else
                return 0;
        });

        return $files;
    }

    private function parseLog ($line)
    {
        if (!empty($line))
        {
            return json_decode($line);
        }
        return null;
    }
}
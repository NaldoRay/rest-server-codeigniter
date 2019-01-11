<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author  Ray Naldo
 */
class Query_log_viewer extends APP_REST_Controller
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
        $this->load->view('query_log_viewer', $data);
    }

    public function logs_get ($logIdx)
    {
        $logs = array();

        $files = $this->getFiles();
        if (isset($files[$logIdx]))
        {
            $file = $files[$logIdx];

            $filePath = $file->getPathname();
            if ($file = fopen($filePath, 'r'))
            {
                while (($line = fgets($file)) !== false)
                {
                    $line = trim($line, " \t\n\r\0\x0B[]");
                    if (empty($line))
                        continue;

                    $parts = preg_split('/(?<!\|)\|(?!\|)/', $line);

                    $date = $parts[0];
                    $ipAddress = null;
                    $query = null;

                    $count = count($parts);
                    if ($count > 1)
                    {
                        $ipAddress = $parts[1];
                        if ($count > 2)
                            $query = $parts[2];
                    }

                    $logs[] = [
                        'date' => $date,
                        'ipAddress' => $ipAddress,
                        'query' => $query
                    ];
                }

                fclose($file);
            }
        }
        // tampilkan baris paling terakhir terlebih dahulu
        $logs = array_reverse($logs);
        $this->respondSuccess($logs);
    }

    private function getFiles ()
    {
        $files = array();

        $logPath = $this->config->item('app_query_log_path');
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
}
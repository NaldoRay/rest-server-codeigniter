<?php

/**
 * Author: Ray N
 * Date: 10/12/2017
 * Time: 11:51
 *
 * @property File_provider $fileProvider
 */
class File_api extends CI_Controller
{
    public function __construct ()
    {
        parent::__construct();
        $this->load->library('file_provider', null, 'fileProvider');
    }

    public function view ($filename)
    {
        $this->fileProvider->viewFile($filename);
    }
}
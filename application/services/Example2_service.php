<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class Example2_service extends APP_Localhost_service
{
    public function __construct ()
    {
        parent::__construct('examples2');
    }

    public function getAllExamples ()
    {
        return $this->get();
    }

    public function addExample ($title, $desc)
    {
        return $this
            ->attachUploadedFile('picture', 'userfile')
            ->addJsonFormData('data', [
                'title' => $title,
                'desc' => $desc
            ])
            ->post();
    }

    public function deleteExample ($id)
    {
        return $this->delete($id);
    }
}
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class Example_service extends APP_Localhost_service
{
    public function getAllExamples ()
    {
        return $this->get();
    }

    public function addExample ($title, $desc)
    {
        return $this->post('examples', [
            'title' => $title,
            'desc' => $desc
        ]);
    }

    public function deleteExample ($id)
    {
        return $this->delete('examples', $id);
    }
}
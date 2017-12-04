<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class MY_Exceptions extends CI_Exceptions
{
    public function show_error ($heading, $message, $template = 'error_general', $status_code = 500)
    {
        header('Content-Type: application/json; charset=utf-8', true, $status_code);
        $response = [
            'error' => [
                'domain' => 'Exception',
                'message' => $message
            ]
        ];

        ob_start();
        echo json_encode($response);
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }
}
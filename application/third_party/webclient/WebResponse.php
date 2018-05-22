<?php

use Psr\Http\Message\ResponseInterface;

/**
 * @author Ray Naldo
 */
class WebResponse
{
    private $response;

    public function __construct (ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    public function isSuccess ()
    {
        $statusCode = $this->getStatusCode();
        return ($statusCode >= 200 && $statusCode <= 299);
    }

    public function getStatusCode ()
    {
        if (is_null($this->response))
            return 500;
        else
            return $this->response->getStatusCode();
    }

    /**
     * @return object|null
     */
    public function getBody ()
    {
        try
        {
            $content = $this->response->getBody()->getContents();
            // response body harus dalam format json
            $object = json_decode($content);
            if (isset($object->error) && is_string($object->error))
            {
                $error = new stdClass();
                $error->type = '';
                $error->message = $object->error;

                $object->error = $error;
            }
            return $object;
        }
        catch (Error $e)
        {
            $body = (object) [
                'error' => (object) [
                    'type' => 'InternalError',
                    'message' => 'Internal Error'
                ]
            ];
            return $body;
        }
        catch (Exception $e)
        {
            $body = (object) [
                'error' => (object) [
                    'type' => 'InternalError',
                    'message' => 'Internal Error'
                ]
            ];
            return $body;
        }
    }
}
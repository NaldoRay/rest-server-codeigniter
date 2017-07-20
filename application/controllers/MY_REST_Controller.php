<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require_once APPPATH . '/libraries/REST_Controller.php';

// use namespace
use Restserver\Libraries\REST_Controller;


class MY_REST_Controller extends REST_Controller
{
    public function __construct ()
    {
        parent::__construct();


		$this->lang->load('user_strings', 'english', FALSE);

        // autoload exceptions
        spl_autoload_register(function ($class)
        {
            include_once (APPPATH.'exceptions/' . $class . '.php');
        });
    }

    protected function handleUncaughtException (Exception $e)
    {
        if ($e instanceof MissingArgumentException || $e instanceof InvalidArgumentException || $e instanceof InvalidFormatException
            || $e instanceof SecurityException)
        {
            $this->respondBadRequest($this->getString('invalid_parameters'));
        }
        else if ($e instanceof AuthorizationException || $e instanceof ResourceNotFoundException)
        {
            $this->respondNotFound($this->getString('resource_not_found'));
        }
        else
        {
            $this->respondInternalError();
        }
    }

    protected function handleUncaughtError (\Error $e)
    {
        if ($e instanceof TypeError || $e instanceof ParseError)
        {
            $this->respondBadRequest($this->getString('invalid_parameters'));
        }
        else
        {
            $this->respondInternalError();
        }
    }

    /*
     * Success Response
     */
    protected function respondAccepted (array $data)
    {
        $this->respondSuccess($data, self::HTTP_ACCEPTED);
    }

    protected function respondNoContent ()
    {
        $this->respondSuccess(null, self::HTTP_NO_CONTENT);
    }

    protected function respondCreated ($data)
    {
        $this->respondSuccess($data, self::HTTP_CREATED);
    }

    protected final function respondSuccess ($data = null, $httpCode = self::HTTP_OK)
    {
        if (is_null($data))
        {
            $response = null;
        }
        else
        {
            $response = array(
                'data' => $data
            );
        }
        $this->response($response, $httpCode);
    }

    /*
     * Error Response
     */
    protected function respondBadRequest ($message = '', $domain = null)
    {
        $this->respondError(self::HTTP_BAD_REQUEST, $message, $domain);
    }

    protected function respondUnauthorized ($message = '', $domain = null)
    {
        $this->respondError(self::HTTP_UNAUTHORIZED, $message, $domain);
    }

    /**
     * @param string $message required for 403 Forbidden response
     * @param string|null $domain
     */
    protected function respondForbidden ($message, $domain = null)
    {
        $this->respondError(self::HTTP_FORBIDDEN, $message, $domain);
    }

    protected function respondNotFound ($message = '', $domain = null)
    {
        $this->respondError(self::HTTP_NOT_FOUND, $message, $domain);
    }

    protected function respondInternalError ($message = '', $domain = null)
    {
        $this->respondError(self::HTTP_INTERNAL_SERVER_ERROR, $message, $domain);
    }

    /**
     * @param $statusCode
     * @param $domain
     * @param $message
     * @param array|null $fields
     */
    protected final function respondError ($statusCode, $message = '', $domain = 'Global', array $fields = null)
    {
        $response = array(
            'error' => [
                'domain' => $domain,
                'message' => $message
            ]
        );
        if (!empty($fields))
        {
            $response['error']['fields'] = $fields;
        }

        $this->response($response, $statusCode);
    }

    public final function response ($data = null, $http_code = null, $continue = false)
    {
        // transform fields for response
        if (!is_null($data))
        {
            $isDefaultErrorResponse = (isset($data['status']) && $data['status'] === false);
            if ($isDefaultErrorResponse)
            {
                // reformat default error response dari REST_Controller
                $this->respondError(
                    $http_code,
                    $data[$this->config->item('rest_message_field_name')],
                    'API'
                );
                return;
            }
        }

        parent::response($data, $http_code, $continue);
    }

    /**
     * @return object
     */
    protected final function checkBasicAuth ()
    {
        $auth = $this->getBasicAuth();
        if (is_null($auth))
        {
            $this->respondUnauthorized($this->getString('invalid_credentials'));
            exit;
        }
        else
        {
            return $auth;
        }
    }

    /**
     * @return object|null {username: string, password: string}
     */
    private final function getBasicAuth ()
    {
        $username = $this->input->server('PHP_AUTH_USER');
        if (is_null($username))
        {
            return null;
        }
        else
        {
            $password = $this->input->server('PHP_AUTH_PW');

            $auth = new stdClass();
            $auth->username = $username;
            $auth->password = $password;
            return $auth;
        }
    }

    protected function checkCrudAuth ()
    {
        $allowedIps = [
            '127.0.0.1', '0.0.0.0', '10.210.1.122',
            '10.1.11.87' // TODO remove ip buat debug
        ];
        if (!in_array($this->input->ip_address(), $allowedIps))
        {
            $this->respondNotFound($this->getString('resource_not_found'));
        }
    }

    protected function getString ($key)
    {
        return $this->lang->line($key);
    }
}

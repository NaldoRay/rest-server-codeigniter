<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require_once(APPPATH . 'libraries/REST_Controller.php');

// use namespace
use Restserver\Libraries\REST_Controller;

/**
 * @property Rest_validation $validation
 */
class MY_REST_Controller extends REST_Controller
{
    protected $modelToResponseFields = [
        'db_field' => 'inputField',
        'another_db_field' => 'anotherInputField'
    ];

    public function __construct ()
    {
        parent::__construct();

        $this->load->helper(['file', 'log']);
        $this->load->library('rest_validation', null, 'validation');

        // autoload exceptions
        spl_autoload_register(function ($class)
        {
            $filepath = APPPATH.'exceptions/' . $class . '.php';
            if (file_exists($filepath))
                include_once($filepath);
        });
    }

    /**
     * Handle uncaught exception from REST method call.
     * @param Exception $e
     */
    protected function handleUncaughtException (Exception $e)
    {
        if ($e instanceof MissingArgumentException || $e instanceof InvalidArgumentException || $e instanceof InvalidFormatException
            || $e instanceof SecurityException)
        {
            $this->respondBadRequest($e->getMessage());
        }
        else if ($e instanceof AuthorizationException || $e instanceof ResourceNotFoundException)
        {
            $this->respondNotFound($e->getMessage());
        }
        else if ($e instanceof BadArrayException)
        {
            $this->respondError(
                self::HTTP_BAD_REQUEST,
                'Validation error',
                $e->getDomain(),
                $e->getAllErrors()
            );
        }
        else if ($e instanceof BadValueException)
        {
            $this->respondBadRequest($e->getMessage(), $e->getDomain());
        }
        else if ($e instanceof CIPHPUnitTestExitException)
        {
            // This block is for ci-phpunit-test
            throw $e;
        }
        else
        {
            $this->respondInternalError(ENVIRONMENT == 'production' ? '' : $e->getMessage());
        }
    }

    /**
     * Handle PHP Error e.g. index out of bound, invalid type, etc.
     * @param Error $e
     */
    protected function handleUncaughtError (\Error $e)
    {
        if ($e instanceof TypeError || $e instanceof ParseError)
        {
            $this->respondBadRequest('Invalid parameters');
        }
        else
        {
            $this->respondInternalError(ENVIRONMENT == 'production' ? '' : $e->getMessage());
        }
    }

    /*
     * Success Response
     */
    /**
     * Send successful response: 202 Accepted.
     * @param array $data
     */
    protected function respondAccepted (array $data)
    {
        $this->respondSuccess($data, self::HTTP_ACCEPTED);
    }

    /**
     * Send successful response: 204 No Content.
     */
    protected function respondNoContent ()
    {
        $this->respondSuccess(null, self::HTTP_NO_CONTENT);
    }

    /**
     * Send successful response: 201 Created.
     * @param $data
     */
    protected function respondCreated ($data)
    {
        $this->respondSuccess($data, self::HTTP_CREATED);
    }

    /**
     * Send custom successful response.
     * @param null $data
     * @param int $httpCode
     */
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
    /**
     * Send error response: 400 Bad Request.
     * @param string $message
     * @param string|null $domain
     */
    protected function respondBadRequest ($message = '', $domain = null)
    {
        $this->respondError(self::HTTP_BAD_REQUEST, $message, $domain);
    }

    /**
     * Send error response: 401 Unauthorized.
     * @param string $message
     * @param string|null $domain
     */
    protected function respondUnauthorized ($message = '', $domain = null)
    {
        $this->respondError(self::HTTP_UNAUTHORIZED, $message, $domain);
    }

    /**
     * Send error response: 403 Forbidden.
     * @param string $message required for 403 Forbidden response
     * @param string|null $domain
     */
    protected function respondForbidden ($message, $domain = null)
    {
        $this->respondError(self::HTTP_FORBIDDEN, $message, $domain);
    }

    /**
     * Send error response: 404 Not Found.
     * @param string $message
     * @param string|null $domain
     */
    protected function respondNotFound ($message = '', $domain = null)
    {
        $this->respondError(self::HTTP_NOT_FOUND, $message, $domain);
    }

    /**
     * Send error response: 500 Internal Error.
     * @param string $message
     * @param string|null $domain
     */
    protected function respondInternalError ($message = '', $domain = null)
    {
        $this->respondError(self::HTTP_INTERNAL_SERVER_ERROR, $message, $domain);
    }

    /**
     * Send custom error response.
     * @param $statusCode
     * @param $domain
     * @param $message
     * @param array|null $fields
     */
    protected final function respondError ($statusCode, $message = '', $domain = 'Global', array $fields = null)
    {
		if (is_null($domain))
            $domain = 'Global';
		
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

    /**
     * Forward/send response coming from another web service.
     * @param $response
     */
    protected function forwardResponse ($response)
    {
        $forwardedResponse = null;
        if ($response->success)
        {
            if (isset($response->data))
            {
                $forwardedResponse = array(
                    'data' => $response->data
                );
            }
        }
        else
        {
            if (isset($response->error))
            {
                $forwardedResponse = array(
                    'error' => $response->error
                );
            }
        }

        $this->response($forwardedResponse, $response->statusCode);
    }

    /**
     * Send custom response.
     * @param mixed|null $data
     * @param int|null $http_code
     * @param bool $continue
     */
    public final function response ($data = null, $http_code = null, $continue = false)
    {
        // transform fields for response
        if (!is_null($data))
        {
            $isDefaultErrorResponse = (is_array($data) && isset($data['status']) && $data['status'] === false);
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
            else
            {
                $data = $this->transformToResponseData($data);
            }
        }

        parent::response($data, $http_code, $continue);
    }

    private function transformToResponseData ($data)
    {
        if (is_array($data) || is_object($data))
        {
            $responseData = array();
            foreach ($data as $key => $value)
            {
                if (isset($this->modelToResponseFields[ $key ]))
                    $field = $this->modelToResponseFields[ $key ];
                else
                    $field = $key;

                $responseData[ $field ] = $this->transformToResponseData($value);
                if (isset($this->booleanModelToResponseFields[$key]))
                    $responseData[ $field ] = (bool) $responseData[ $field ];
            }

            return $responseData;
        }
        else
        {
            return $data;
        }
    }

    /**
     * Filter request data which will only return allowed fields.
     * @param $requestData
     * @param array|null $filterMap
     * @return array request data that has been filtered and mapped to model's field
     */
    protected function getModelData ($requestData, array $filterMap)
    {
        if (is_array($requestData) || is_object($requestData))
        {
            $requestToModelFields = $filterMap;

            $modelData = array();
            foreach ($requestData as $key => $value)
            {
                if (isset($requestToModelFields[ $key ]))
                {
                    $field = $requestToModelFields[ $key ];
                    $modelData[ $field ] = $this->getModelData($value, $filterMap);
                }
            }
            return $modelData;
        }
        else
        {
            return $requestData;
        }
    }

    protected function getModelOrder ($requestOrder, array $filterMap)
    {
        if (is_null($requestOrder))
            return null;

        $orderFields = explode(',', $requestOrder);
        $orders = array();
        foreach ($orderFields as $orderField)
        {
            if (!empty($orderField))
            {
                if ($orderField[0] == '-')
                {
                    $field = substr($orderField, 1);
                    $dir = 'desc';
                }
                else
                {
                    $field = $orderField;
                    $dir = 'asc';
                }

                if (isset($filterMap[ $field ]))
                {
                    $field = $filterMap[ $field ];
                    $orders[] = sprintf('%s %s', $field, $dir);
                }
            }
        }

        return implode(',', $orders);
    }

    /**
     * @return object
     */
    protected final function checkBasicAuth ()
    {
        $auth = $this->getBasicAuth();
        if (is_null($auth))
        {
            $this->respondUnauthorized('Invalid credentials');
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
            $this->respondNotFound('Not found');
        }
    }
}

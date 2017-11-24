<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require_once(APPPATH . 'libraries/REST_Controller.php');

// use namespace
use Restserver\Libraries\REST_Controller;

/**
 * @author Ray Naldo
 * @property Rest_validation $validation
 */
class MY_REST_Controller extends REST_Controller
{
    protected static $LANG_ENGLISH = 'english';
    protected static $LANG_INDONESIA = 'indonesia';

    // this property is for debugging-only
    /** @var  ContextErrorException */
    private $contextError;


    public function __construct ()
    {
        parent::__construct();

        $this->load->helper(['file', 'log']);
        $this->load->library(Rest_validation::class, null, 'validation');

        $this->initLanguage();

        set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext)
        {
            // error was suppressed with the @-operator
            if (error_reporting() === 0)
                return false;

            // skip first backtrace i.e. this file
            $backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);
            throw new ContextErrorException($errstr, 0, $errno, $errfile, $errline, $errcontext, $backtrace);
        });
    }

    private function initLanguage ()
    {
        $language = $this->input->get_request_header('Accept-Language');
        if ($language == 'id')
            $this->setLanguage(self::$LANG_INDONESIA);
        else // default
            $this->setLanguage(self::$LANG_ENGLISH);
    }

    protected function setLanguage ($language)
    {
        $this->lang->load('messages', $language);
    }

    /**
     * Handle uncaught exception from REST method call.
     */
    protected function handleUncaughtException (Exception $e)
    {
        if ($e instanceof ApiException)
        {
            if ($e instanceof MissingArgumentException || $e instanceof InvalidFormatException
                || $e instanceof SecurityException)
            {
                $this->respondBadRequest($e->getMessage(), $e->getDomain());
            }
            else if ($e instanceof AuthorizationException || $e instanceof ResourceNotFoundException)
            {
                $this->respondNotFound($e->getMessage(), $e->getDomain());
            }
            else if ($e instanceof BadBatchArrayException)
            {
                $this->respondError(
                    self::HTTP_BAD_REQUEST,
                    $this->getString('msg_validation_error'),
                    $e->getDomain(),
                    null,
                    $e->getBatchErrors()
                );
            }
            else if ($e instanceof BadArrayException)
            {
                $this->respondError(
                    self::HTTP_BAD_REQUEST,
                    $this->getString('msg_validation_error'),
                    $e->getDomain(),
                    $e->getAllErrors()
                );
            }
            else if ($e instanceof BadValueException)
            {
                $this->respondBadRequest($e->getMessage(), $e->getDomain());
            }
            else
            {
                $this->respondInternalError($e->getMessage(), $e->getDomain());
            }
        }
        else if ($e instanceof InvalidArgumentException)
        {
            $this->respondBadRequest($e->getMessage());
        }
        else if ($e instanceof ContextErrorException)
        {
            $this->contextError = $e;

            $context = $e->getContext();
            if (is_array($context) && isset($context['sql']))
            {
                // hanya berlaku untuk Oracle SQL
                $message = $e->getMessage();
                if (preg_match('/unique constraint \\(.+PK.+\\) violated/', $message))
                    $errorMessage = $this->getString('msg_unique_constraint');
                else if (preg_match('/integrity constraint \\(.+FK.+\\) violated - (parent key not found|child record found)/', $message, $matches))
                {
                    if ($matches[1] == 'parent key not found')
                        $errorMessage = $this->getString('msg_parent_not_found');
                    else if ($matches[1] == 'child record found')
                        $errorMessage = $this->getString('msg_child_found');
                    else
                        $errorMessage = $this->getString('msg_integrity_constraint');
                }
                else if (preg_match('/value too large/', $message))
                    $errorMessage = $this->getString('msg_value_too_large');
                else
                    $errorMessage = $this->getString('msg_database_error');

                $this->respondBadRequest($errorMessage, null);
            }
            else
            {
                $this->respondInternalError('Internal Error', null);
            }

            $this->respondInternalError($errorMessage, null);
        }
        else if ($e instanceof CIPHPUnitTestExitException)
        {
            // This block is for ci-phpunit-test
            throw $e;
        }
        else
        {
            $this->respondInternalError(ENVIRONMENT === 'production' ? 'Internal Error' : $e->getMessage());
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
        $this->respondError(self::HTTP_BAD_REQUEST, $message, $domain, null);
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
     * @param string $message
     * @param string $domain
     * @param array|null $fields
     * @param array|null $batchFields
     */
    protected final function respondError ($statusCode, $message = '', $domain = 'Global', array $fields = null, array $batchFields = null)
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
        if (!empty($batchFields))
        {
            $response['error']['batchFields'] = $batchFields;
        }
        if (ENVIRONMENT !== 'production')
        {
            if (!is_null($this->contextError))
            {
                $response['error']['debug'] = [
                    'message' => $this->contextError->getMessage(),
                    'location' => $this->contextError->getFile() .' at line '. $this->contextError->getLine(),
                    'context' => $this->contextError->getContext(),
                    'backtrace' => $this->contextError->getBacktrace()
                ];
            }
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
        if (!is_null($data))
        {
            $isDefaultErrorResponse = (is_array($data) && isset($data['status']) && $data['status'] === false);
            if ($isDefaultErrorResponse)
            {
                // reformat default error response from REST_Controller
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

    protected function getQueryFields ()
    {
        $fieldsParam = $this->input->get('fields');
        if (is_null($fieldsParam))
            $fields = array();
        else
            $fields = explode(',', $fieldsParam);

        return $fields;
    }

    protected function getQuerySorts ()
    {
        $sortsParam = $this->input->get('sorts');
        if (is_null($sortsParam))
            $sorts = array();
        else
            $sorts = explode(',', $sortsParam);

        return $sorts;
    }

    /**
     * @return array
     */
    protected function getQueryFilters ()
    {
        $filtersParam = $this->input->get('filters');
        if (!is_array($filtersParam))
            $filtersParam = array();

        return $filtersParam;
    }

    /**
     * @return bool
     */
    protected function isUniqueQuery ()
    {
        $unique = $this->input->get('unique');
        return ($unique === 'true');
    }

    public function post ($key = NULL, $xss_clean = NULL)
    {
        $data = parent::post($key, $xss_clean);
        if (is_null($key))
            $data = $this->processData($data);

        return $data;
    }

    public function put ($key = NULL, $xss_clean = NULL)
    {
        $data = parent::put($key, $xss_clean);
        if (is_null($key))
            $data = $this->processData($data);

        return $data;
    }

    public function patch ($key = NULL, $xss_clean = NULL)
    {
        $data = parent::patch($key, $xss_clean);
        if (is_null($key))
            $data = $this->processData($data);

        return $data;
    }

    /**
     * Process data from request body.
     * Auto-decode json metadata on multipart request.
     * @param array $data
     * @return array
     */
    protected function processData (array $data)
    {
        if (!empty($data))
        {
            // auto-decode json metadata on multipart request
            $contentType = $this->input->get_request_header('Content-Type');
            if (strpos($contentType, 'multipart/form-data') === 0)
            {
                // metadata must be string of json object
                if (isset($data['data']) && is_string($data['data']))
                {
                    $data = json_decode($data['data'], true);
                    if (json_last_error() != JSON_ERROR_NONE || !is_array($data))
                        $data = array();
                }
                else
                {
                    $data = array();
                }
            }
        }

        return $data;
    }

    protected function getAll (MY_Model $model, array $extraFilters = array())
    {
        $fields = $this->getQueryFields();
        $filters = $this->getQueryFilters();
        $sorts = $this->getQuerySorts();
        $unique = $this->isUniqueQuery();

        if (!empty($extraFilters))
            $filters = array_merge($filters, $extraFilters);

        return $model->getAll($fields, $filters, $sorts, $unique);
    }

    protected function getString ($key)
    {
        $line = $this->lang->line($key);
        if ($line === false)
            return null;
        else
            return $line;
    }
}

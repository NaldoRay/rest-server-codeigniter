<?php
defined('BASEPATH') OR exit('No direct script access allowed');

requireClass(REST_Controller::class, 'core/libraries/');

// use namespace
use Restserver\Libraries\REST_Controller;

/**
 * @author Ray Naldo
 * @property Rest_validation $validation
 */
class MY_REST_Controller extends REST_Controller
{
    protected static $LANG_ENGLISH = 'english';

    /** @var RestAccess */
    private $restAccess;

    // this property is for debugging-only
    /** @var  ContextErrorException */
    private $contextError;


    public function __construct ()
    {
        parent::__construct();

        $language = $this->getLanguage();
        $this->setLanguage($language);
        $this->lang->load('messages');

        set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext)
        {
            // error was suppressed with the @-operator
            if (error_reporting() === 0)
                return false;

            // skip first backtrace i.e. this file
            $backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), 1);
            $exception = new ContextErrorException($errstr, 0, $errno, $errfile, $errline, $errcontext, $backtrace);

            $this->handleContextError($exception);
            // bypass PHP error handler
            return true;
        });

        // fix "Fatal error: Class 'CI_Model' not found"
        require_once(BASEPATH.'core/Model.php');

        try
        {
            $this->restAccess = new RestAccess();
            $this->restAccess->check();

            if ($this->shouldAuthorizeClient())
                $this->authorizeClient();
        }
        catch (AuthorizationException $e)
        {
            $this->respondForbidden($e->getMessage(), 'API');
            exit;
        }

        $this->load->library(Rest_validation::class, null, 'validation');
    }

    protected function getLanguage ()
    {
        return self::$LANG_ENGLISH;
    }

    private function setLanguage ($language)
    {
        $this->config->set_item('language', $language);
    }

    protected function shouldAuthorizeClient ()
    {
        return $this->restAccess->shouldAuthorizeClient();
    }

    protected function authorizeClient ()
    {
        // do nothing
    }

    private function handleContextError (ContextErrorException $exception)
    {
        $this->contextError = $exception;
        $context = $exception->getContext();
        if (is_array($context) && isset($context['sql']))
        {
            // only valid for Oracle SQL
            $message = $exception->getMessage();
            if (preg_match('/unique constraint \\(.+\\) violated/', $message))
            {
                $errorMessage = $this->getString('msg_unique_constraint');
                $this->respondBadRequest($errorMessage);
            }
            else if (preg_match('/integrity constraint \\(.+FK.+\\) violated - (parent key not found|child record found)/', $message, $matches))
            {
                if ($matches[1] == 'parent key not found')
                    $errorMessage = $this->getString('msg_parent_not_found');
                else if ($matches[1] == 'child record found')
                    $errorMessage = $this->getString('msg_child_found');
                else
                    $errorMessage = $this->getString('msg_integrity_constraint');

                $this->respondBadRequest($errorMessage);
            }
            else if (preg_match('/value too large/', $message))
            {
                $errorMessage = $this->getString('msg_value_too_large');
                $this->respondBadRequest($errorMessage);
            }
            else
            {
                $errorMessage = $this->getString('msg_database_error');
                $this->respondInternalError($errorMessage);
            }
        }
        else
        {
            $this->respondInternalError('Internal Error');
        }

        exit;
    }

    /**
     * Handle uncaught exception from REST method call.
     * @param Exception $e
     */
    protected function handleUncaughtException (Exception $e)
    {
        if ($e instanceof ApiException)
        {
            if ($e instanceof AuthenticationException)
            {
                $this->respondUnauthorized($e->getMessage(), $e->getDomain());
            }
            else if ($e instanceof AuthorizationException)
            {
                $this->respondForbidden($e->getMessage(), $e->getDomain());
            }
            else if ($e instanceof ResourceNotFoundException)
            {
                $this->respondNotFound($e->getMessage(), $e->getDomain());
            }
            else if ($e instanceof SecurityException
                || $e instanceof BadFormatException
                || $e instanceof BadValueException)
            {
                $this->respondBadRequest($e->getMessage(), $e->getDomain());
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
            else if ($e instanceof InvalidStateException)
            {
                $this->respondError(
                    self::HTTP_CONFLICT,
                    $e->getMessage(),
                    $e->getDomain()
                );
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
     * @param int $statusCode
     */
    protected final function respondSuccess ($data = null, $statusCode = self::HTTP_OK)
    {
        if (is_null($data))
        {
            $response = null;
        }
        else
        {
            $requestMethod = $this->input->method(true);
            $filterData = ($requestMethod == 'GET')
                || ($requestMethod == 'POST' && endsWith($this->uri->uri_string(), 'search'));

            if ($filterData)
                $data = $this->filterData($data);

            $response = array(
                'data' => $data
            );
        }
        $this->response($response, $statusCode);
    }

    private function filterData ($data)
    {
        if (empty($data))
            return $data;

        $fieldsFilter = $this->getQueryFieldsFilter();
        $data = $this->filterObjectFields($data, $fieldsFilter);

        return $data;
    }

    private function filterObjectFields ($data, FieldsFilter $fieldsFilter)
    {
        if ($fieldsFilter->isEmpty())
            return $data;

        if (is_array($data) )
        {
            $filteredData = array();
            if ($this->isSequentialArray($data))
            {
                foreach ($data as $idx => $row)
                {
                    $filteredData[$idx] = $this->filterObjectFields($row, $fieldsFilter);
                }
            }
            else
            {
                foreach ($data as $field => $value)
                {
                    if ($fieldsFilter->fieldExists($field))
                    {
                        $subFieldsFilter = $fieldsFilter->getFieldsFilter($field);
                        $filteredData[ $field ] = $this->filterObjectFields($value, $subFieldsFilter);
                    }
                }
            }
            return $filteredData;
        }
        else if (is_object($data))
        {
            $filteredData = array();
            foreach ($data as $field => $value)
            {
                if ($fieldsFilter->fieldExists($field))
                {
                    $subFieldsFilter = $fieldsFilter->getFieldsFilter($field);
                    $filteredData[ $field ] = $this->filterObjectFields($value, $subFieldsFilter);
                }
            }
            return (object) $filteredData;
        }
        else
        {
            return $data;
        }
    }

    private function isSequentialArray (array $arr)
    {
        foreach ($arr as $key => $value)
        {
            if (is_string($key))
                return false;
        }
        return true;
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

        $error = array(
            'domain' => $domain,
            'message' => $message
        );
        if (!empty($fields))
        {
            $error['fields'] = $fields;
        }
        if (!empty($batchFields))
        {
            $error['batchFields'] = $batchFields;
        }
        if (!is_null($this->contextError))
        {
            $error['debug'] = [
                'message' => $this->contextError->getMessage(),
                'location' => $this->contextError->getFile() .' at line '. $this->contextError->getLine(),
                'context' => $this->contextError->getContext(),
                'backtrace' => $this->contextError->getBacktrace()
            ];

            // error has context, log this error
            logContextError($statusCode, $error);

            if (ENVIRONMENT === 'production')
            {
                unset($error['debug']);
            }
        }

        $response = array(
            'error' => $error
        );
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
     * @param int|null $statusCode
     * @param bool $continue
     */
    public final function response ($data = null, $statusCode = null, $continue = false)
    {
        if (!is_null($data))
        {
            $isDefaultErrorResponse = (is_array($data) && isset($data['status']) && $data['status'] === false);
            if ($isDefaultErrorResponse)
            {
                // reformat default error response from REST_Controller
                $this->respondError(
                    $statusCode,
                    $data[$this->config->item('rest_message_field_name')],
                    'API'
                );
                return;
            }
        }

        parent::response($data, $statusCode, $continue);
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

    public function post ($key = NULL, $xss_clean = NULL)
    {
        $data = parent::post($key, $xss_clean);
        if (is_null($key))
            $data = $this->processData($data);

        return $data;
    }

    public function put ($key = NULL, $xss_clean = NULL)
    {
        $contentType = $this->input->get_request_header('Content-Type');
        if (strpos($contentType, 'multipart/form-data') === 0)
            $data = $this->parseMultipartFormData();
        else
            $data = parent::put($key, $xss_clean);

        if (is_null($key))
            $data = $this->processData($data);

        return $data;
    }

    public function patch ($key = NULL, $xss_clean = NULL)
    {
        $contentType = $this->input->get_request_header('Content-Type');
        if (strpos($contentType, 'multipart/form-data') === 0)
            $data = $this->parseMultipartFormData();
        else
            $data = parent::patch($key, $xss_clean);

        if (is_null($key))
            $data = $this->processData($data);

        return $data;
    }

    /**
     * Parser for PATCH/PUT request with multipart/form-data content-type.
     * We need to parse manually because PHP only auto-parse multipart/form-data for POST method.
     * https://stackoverflow.com/questions/9464935/php-multipart-form-data-put-request
     * https://bugs.php.net/bug.php?id=55815&thanks=6
     */
    private function parseMultipartFormData ()
    {
        // Fetch content and determine boundary
        $raw_data = file_get_contents('php://input');
        if (empty($raw_data))
        {
            return array();
        }
        else
        {
            $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

            // Fetch each part
            $parts = array_slice(explode($boundary, $raw_data), 1);
            $data = array();

            foreach ($parts as $part)
            {
                // If this is the last part, break
                if ($part == "--\r\n")
                    break;

                // Separate content from headers
                $part = ltrim($part, "\r\n");
                list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

                // Parse the headers list
                $raw_headers = explode("\r\n", $raw_headers);
                $headers = array();
                foreach ($raw_headers as $header)
                {
                    list($name, $value) = explode(':', $header);
                    $headers[ strtolower($name) ] = ltrim($value, ' ');
                }

                // Parse the Content-Disposition to get the field name, etc.
                if (isset($headers['content-disposition']))
                {
                    $filename = null;
                    preg_match(
                        '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                        $headers['content-disposition'],
                        $matches
                    );
                    list(, $type, $name) = $matches;

                    // check if content is a file
                    if (isset($matches[4]))
                    {
                        // parse file
                        $filename = $matches[4];

                        $filename_parts = pathinfo($filename);
                        $tmp_name = tempnam(ini_get('upload_tmp_dir'), $filename_parts['filename']);
                        $type = $headers['content-type'];

                        $_FILES[ $matches[2] ] = array(
                            'error' => 0,
                            'name' => $filename,
                            'tmp_name' => $tmp_name,
                            'size' => strlen($body),
                            'type' => $type
                        );

                        file_put_contents($tmp_name, $body);
                    }
                    else
                    {
                        $data[ $name ] = substr($body, 0, strlen($body) - 2);
                    }
                }
            }

            return $data;
        }
    }

    /**
     * Process data from request body.
     * Auto-decode json metadata on multipart request.
     * @param array $data
     * @return array
     */
    private function processData (array $data)
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

    protected function getAll (Queriable $queriable, QueryParam $queryParam = null)
    {
        if (is_null($queryParam))
            $queryParam = $this->getQueryParam();

        return $queriable->query($queryParam);
    }

    /**
     * @return QueryParam
     */
    protected function getQueryParam ()
    {
        // non-search query param, no condition
        $filters = $this->getQueryFilters();
        $expands = $this->getQueryExpands();
        $sorts = $this->getQuerySorts();
        $limit = $this->getQueryLimit();
        $offset = $this->getQueryOffset();

        return QueryParam::createFilter($filters)
            ->expand($expands)
            ->sort($sorts)
            ->limit($limit, $offset);
    }

    protected function search (Queriable $searchable, QueryParam $searchParam = null)
    {
        if (is_null($searchParam))
            $searchParam = $this->getSearchParam();

        return $searchable->query($searchParam);
    }

    /**
     * @return QueryParam
     * @throws BadArrayException
     * @throws BadBatchArrayException
     * @throws BadValueException
     */
    protected function getSearchParam ()
    {
        // search must be POST
        $searchData = $this->post();

        $condition = $this->parseSearchCondition($searchData);
        $expands = $this->getQueryExpands();
        $sorts = $this->getQuerySorts();
        $limit = $this->getQueryLimit();
        $offset = $this->getQueryOffset();

        return QueryParam::createSearch($condition)
            ->expand($expands)
            ->sort($sorts)
            ->limit($limit, $offset);
    }

    private function parseSearchCondition (array $search)
    {
        if (isset($search['field']))
        {
            $this->validation->forArray($search);
            $this->validation->field('field')
                ->required()
                ->notEmpty()
                ->onlyString();
            $this->validation->field('operator')
                ->required()
                ->onlyOneOf(['=', '!=', '<', '<=', '>', '>=', '~', '!~']);
            $this->validation->field('value')
                ->required()
                ->nullable();
            $this->validation->validate();

            $field = $search['field'];
            $operator = $search['operator'];
            $value = $search['value'];

            switch ($operator)
            {
                case '=':
                    $search = new EqualsCondition($field, $value);
                    break;
                case '!=':
                    $search = new NotEqualsCondition($field, $value);
                    break;
                case '<':
                    $search = new LessThanCondition($field, $value);
                    break;
                case '<=':
                    $search = new LessEqualsCondition($field, $value);
                    break;
                case '>':
                    $search = new GreaterThanCondition($field, $value);
                    break;
                case '>=':
                    $search = new GreaterEqualsCondition($field, $value);
                    break;
                case '~':
                    $search = new ContainsCondition($field, $value, true);
                    break;
                case '~~':
                    $search = new ContainsCondition($field, $value, false);
                    break;
                case '!~':
                    $search = new NotContainsCondition($field, $value, true);
                    break;
                case '!~~':
                    $search = new NotContainsCondition($field, $value, false);
                    break;
                default:
                    return array();
            }

            return $search;
        }
        else if (isset($search['logicalOperator']))
        {
            $this->validation->forArray($search);
            $this->validation->field('logicalOperator')
                ->required()
                ->onlyOneOf(['AND', 'OR']);
            $this->validation->field('conditions')
                ->required()
                ->onlyArrayOfAssociatives();
            $this->validation->validate();

            $logicalOperator = $search['logicalOperator'];
            $conditions = $search['conditions'];

            foreach ($conditions as $idx => $condition)
                $conditions[$idx] = $this->parseSearchCondition($condition);

            if ($logicalOperator == 'AND')
                return LogicalCondition::logicalAnd($conditions);
            else
                return LogicalCondition::logicalOr($conditions);
        }

        throw new BadValueException($this->getString('msg_search_invalid'));
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

    private function getQueryFieldsFilter ()
    {
        $fieldsParam = $this->input->get('fields');
        return FieldsFilter::fromString($fieldsParam);
    }

    protected function getQueryExpands ()
    {
        $expandsParam = $this->input->get('expands');
        if (is_null($expandsParam))
            $expands = array();
        else
            $expands = preg_split('/(?!\(\w*),(?![\w,]*\))/', $expandsParam);

        return $expands;
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
     * @return int
     */
    protected function getQueryLimit ()
    {
        $limit = $this->input->get('limit');
        try
        {
            return $this->validation->tryParseInteger($limit, null);
        }
        catch (BadValueException $e)
        {
            return -1;
        }
    }

    /**
     * @return int
     */
    protected function getQueryOffset ()
    {
        $offset = $this->input->get('offset');
        try
        {
            return $this->validation->tryParseInteger($offset, null);
        }
        catch (BadValueException $e)
        {
            return 0;
        }
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

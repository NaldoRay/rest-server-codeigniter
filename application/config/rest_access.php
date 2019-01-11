<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Author: Ray Naldo
 */

defined('HTTP_GET') OR define('HTTP_GET', 'GET');
defined('HTTP_POST') OR define('HTTP_POST', 'POST');
defined('HTTP_PUT') OR define('HTTP_PUT', 'PUT');
defined('HTTP_PATCH') OR define('HTTP_PATCH', 'PATCH');
defined('HTTP_DELETE') OR define('HTTP_DELETE', 'DELETE');
defined('HTTP_HEAD') OR define('HTTP_HEAD', 'HEAD');
defined('HTTP_OPTIONS') OR define('HTTP_OPTIONS', 'OPTIONS');

defined('REST_ALL_URIS') OR define('REST_ALL_URIS', '.*');
defined('REST_ALL_METHODS') OR define('REST_ALL_METHODS', '.+');

$config['rest_auth_clients'] = [];

$config['rest_client_access'] = [
    // example for application
    '0.0.0.0' => [
        [
            // all uri except request & query logs viewer
            'uris' => ['^(?!request-logs|query-logs).+$'], // array of allowed uri string, allow regex
            'methods' => [REST_ALL_METHODS] // array of allowed http method constants, allow regex
        ]
    ],
    // example for developer
    '100.100.100.100' => [
        [
            'uris' => [REST_ALL_URIS], // array of allowed uri string, allow regex
            'methods' => [REST_ALL_METHODS] // array of allowed http method constants, allow regex
        ]
    ]
];

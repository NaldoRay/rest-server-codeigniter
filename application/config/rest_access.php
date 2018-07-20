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

defined('REST_ALL_URIS') OR define('REST_ALL_URIS', '*');
defined('REST_ALL_METHODS') OR define('REST_ALL_METHODS', '*');

$config['rest_auth_clients'] = [];

$config['rest_client_access'] = [
    // localhost
    '127.0.0.1' => [
        [
            'uris' => [REST_ALL_URIS], // array of uri string, allow wildcards
            'methods' => [REST_ALL_METHODS] // array of http method constants, allow wildcards
        ]
    ]
];

   <?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Author: Ray Naldo
 */

const HTTP_GET = 'GET';
const HTTP_POST = 'POST';
const HTTP_PUT = 'PUT';
const HTTP_PATCH = 'PATCH';
const HTTP_DELETE = 'DELETE';
const HTTP_HEAD = 'HEAD';
const HTTP_OPTIONS = 'OPTIONS';

const REST_ALL_URIS = ['*']; // allow wildcards
const REST_ALL_METHODS = [HTTP_GET, HTTP_POST, HTTP_PUT, HTTP_PATCH, HTTP_DELETE, HTTP_HEAD, HTTP_OPTIONS];


$config['rest_auth_clients'] = [];

$config['rest_client_access'] = [
    // localhost
    '127.0.0.1' => [
        [
            'uris' => REST_ALL_URIS, // array of string, allow wildcards
            'methods' => REST_ALL_METHODS
        ]
    ]
];

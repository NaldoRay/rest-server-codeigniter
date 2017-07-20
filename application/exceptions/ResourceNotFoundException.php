<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User: RN
 * Date: 5/3/2017
 * Time: 13:42
 */
class ResourceNotFoundException extends Exception
{
    public function __construct ($resourceName)
    {
        parent::__construct(sprintf('%s not found', $resourceName));
    }
}
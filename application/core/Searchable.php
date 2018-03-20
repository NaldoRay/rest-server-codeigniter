<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
interface Searchable
{
    /**
     * @param SearchParam $param
     * @return object[]
     */
    public function search (SearchParam $param);
}
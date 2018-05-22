<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
interface Queriable
{
    /**
     * @param QueryParam $param
     * @return object[]
     */
    public function query (QueryParam $param = null);
}
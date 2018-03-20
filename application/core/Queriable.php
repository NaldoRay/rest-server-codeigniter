<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
interface Queriable
{
    /**
     * @param array $filters
     * @param array $searches
     * @param FieldsFilter $fieldsFilter
     * @param array $sorts
     * @param int $limit
     * @param int $offset
     * @return object[]
     */
    public function query (array $filters = null, array $searches = null, FieldsFilter $fieldsFilter = null, array $sorts = null, $limit = -1, $offset = 0);
}
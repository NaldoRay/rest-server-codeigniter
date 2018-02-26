<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once('QueryCondition.php');

/**
 * @author Ray Naldo
 */
interface Queriable
{
    /**
     * @param array $filters
     * @param array $searches
     * @param array|null $sorts
     * @param int $limit
     * @param int $offset
     * @return object[]
     */
    public function query (array $filters = null, array $searches = null, array $sorts = null, $limit = -1, $offset = 0);

    /**
     * @param QueryCondition $condition
     * @param array|null $sorts
     * @param int $limit
     * @param int $offset
     * @return object[]
     */
    public function search (QueryCondition $condition, array $sorts = null, $limit = -1, $offset = 0);
}
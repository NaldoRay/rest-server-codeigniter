<?php

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 15:24
 */
interface Validation
{
    /**
     * Run validation.
     * @return bool true if success, false if validation failed
     */
    public function validate ();

    /**
     * @return string
     */
    public function getError ();
}
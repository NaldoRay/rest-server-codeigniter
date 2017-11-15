<?php

/**
 * @author Ray Naldo
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
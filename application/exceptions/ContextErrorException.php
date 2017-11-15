<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Ray Naldo
 */
class ContextErrorException extends ErrorException
{
    private $context;
    private $backtrace;

    public function __construct ($message = "", $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__,
                                 $context = '', array $backtrace = array(), Exception $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
        $this->context = $context;
        $this->backtrace = $backtrace;
    }

    /**
     * @return string
     */
    public function getContext ()
    {
        return $this->context;
    }

    public function getBacktrace ()
    {
        return $this->backtrace;
    }
}
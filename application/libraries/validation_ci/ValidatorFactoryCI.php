<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'libraries/validation/ValidatorFactory.php');
include_once('ValueValidatorCI.php');
include_once('FileValidatorCI.php');

/**
 * @author Ray Naldo
 */
class ValidatorFactoryCI extends ValidatorFactory
{
    public function createValueValidator ($value, $label = 'Value')
    {
        return new ValueValidatorCI($value, $label);
    }

    public function createFileValidator ($file, $label = 'File')
    {
        return new FileValidatorCI($file, $label);
    }
}
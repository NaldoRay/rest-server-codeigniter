<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'third_party/validation/ValidatorFactory.php');
include_once('ArrayValueValidatorCI.php');
include_once('ValueValidatorCI.php');
include_once('FileValidatorCI.php');

/**
 * @author Ray Naldo
 */
class ValidatorFactoryCI extends ValidatorFactory
{
    public function createArrayValueValidator (array $arr, $field, $label = 'Value')
    {
        return new ArrayValueValidatorCI($arr, $field, $label);
    }

    public function createValueValidator ($value, $label = 'Value')
    {
        return new ValueValidatorCI($value, $label);
    }

    public function createFileValidator ($filePath, $label = 'File')
{
        return new FileValidatorCI($filePath, $label);
    }
}
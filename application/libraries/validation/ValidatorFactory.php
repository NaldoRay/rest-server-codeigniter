<?php

include_once('ArrayValidator.php');
include_once('BatchArrayValidator.php');
include_once('ValueValidator.php');
include_once('FilesValidator.php');
include_once('FileValidator.php');

/**
 * @author Ray Naldo
 */
class ValidatorFactory
{
    /**
     * @param array $batchArr
     * @return BatchArrayValidator
     */
    public function createBatchArrayValidator (array $batchArr)
    {
        return new BatchArrayValidator($this, $batchArr);
    }

    /**
     * @param array $arr
     * @return ArrayValidator
     */
    public function createArrayValidator (array $arr)
    {
        return new ArrayValidator($this, $arr);
    }

    /**
     * @param $value
     * @param string $label
     * @return ValueValidator
     */
    public function createValueValidator ($value, $label = 'Value')
    {
        return new ValueValidator($value, $label);
    }

    /**
     * @return FilesValidator
     */
    public function createFilesValidator ()
    {
        return new FilesValidator($this);
    }

    /**
     * @param array $file
     * @param string $label
     * @return FileValidator
     */
    public function createFileValidator ($file, $label = 'File')
    {
        return new FileValidator($file, $label);
    }
}
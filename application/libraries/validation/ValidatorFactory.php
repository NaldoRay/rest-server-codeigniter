<?php

include_once('BatchArrayValidator.php');
include_once('ArrayValidator.php');
include_once('ArrayValueValidator.php');
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
     * @param array $arr
     * @param string $field
     * @param string $label
     * @return ArrayValueValidator
     */
    public function createArrayValueValidator (array $arr, $field, $label = 'Value')
    {
        return new ArrayValueValidator($arr, $field, $label);
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
     * @param string $filePath
     * @param string $label
     * @return FileValidator
     */
    public function createFileValidator ($filePath, $label = 'File')
    {
        return new FileValidator($filePath, $label);
    }
}
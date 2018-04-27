<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(__DIR__.'/../FileValidator.php');

/**
 * $_FILES['userfile'] structure: http://php.net/manual/en/features.file-upload.post-method.php
 *
 * @author Ray Naldo
 */
class FileValidatorCI extends FileValidator
{
    private $CI;

    public function __construct ($filePath, $label = 'File')
    {
        parent::__construct($filePath, $label);
        $this->CI = get_instance();
    }

    public function required ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_file_required');

        return parent::required($errorMessage);
    }

    public function allowTypes (array $types, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_file_types');

        return parent::allowTypes($types, $errorMessage);
    }

    public function maxSize ($sizeKB, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = $this->getString('validation_file_size_max');

        return parent::maxSize($sizeKB, $errorMessage);
    }

    private function getString ($key)
    {
        $line = $this->CI->lang->line($key);
        if ($line === false)
            return null;
        else
            return $line;
    }
}
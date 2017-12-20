<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'libraries/validation/FileValidator.php');

/**
 * $_FILES['userfile'] structure: http://php.net/manual/en/features.file-upload.post-method.php
 *
 * @author Ray Naldo
 */
class FileValidatorCI extends FileValidator
{
    public function required ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus diisi';

        return parent::required($errorMessage);
    }

    public function allowTypes (array $types, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = 'Tipe file yang diizinkan: {types}';

        return parent::allowTypes($types, $errorMessage);
    }

    public function maxSize ($sizeKB, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = 'Ukuran file maksimal {size}';

        return parent::maxSize($sizeKB, $errorMessage);
    }
}
<?php

require_once('Validation.php');

/**
 * $_FILES['userfile'] structure: http://php.net/manual/en/features.file-upload.post-method.php
 *
 * @author Ray Naldo
 */
class FileValidator implements Validation
{
    private static $IDX_REQUIRED = 0;
    private static $IDX_ALLOW_TYPES = 1;
    private static $IDX_SIZE_MAX = 2;

    private static $IDX_OTHER = 10;

    /** @var mixed */
    private $file;
    /** @var string */
    private $label;

    /** @var array */
    private $validations;
    /** @var array */
    private $errorMessages;
    /** @var string */
    private $error;


    public function __construct ($file, $label = null)
    {
        if (is_null($label))
            $label = 'Value';

        $this->file = $file;
        $this->label = $label;

        $this->validations = array();
        $this->errorMessages = array();
        $this->error = null;
    }

    /**
     * @return $this
     */
    public function required ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf('%s is required', $this->label);

        $this->setValidation(self::$IDX_REQUIRED, function ($value)
        {
            return isset($value);
        }, $errorMessage);

        return $this;
    }

    public function allowTypes (array $types, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf("%s's file type must be one of: %s", $this->label, implode(', ', $types));

        $this->setValidation(self::$IDX_ALLOW_TYPES, function ($file) use ($types)
        {
            $mime = mime_content_type($file['tmp_name']);
            $mimes = self::getMimes();
            foreach ($types as $type)
            {
                if (is_array($mimes[$type]))
                {
                    if (in_array($mime, $mimes[$type], true))
                        return true;
                }
                else if ($mime === $mimes[$type])
                {
                    return true;
                }
            }
            return false;
        }, $errorMessage);

        return $this;
    }

    /**
     * @param int $sizeKB
     * @param null $errorMessage
     * @return $this
     */
    public function maxSize ($sizeKB, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = sprintf("%s's max file size is %d KB", $this->label, $sizeKB);

        $this->setValidation(self::$IDX_SIZE_MAX, function ($file) use ($sizeKB)
        {
            $sizeByte = $sizeKB * 1024;
            return ($file['size'] <= $sizeByte);
        }, $errorMessage);

        return $this;
    }

    /**
     * @param Closure $validation
     * @param string|Closure $errorMessage error message or function to return error message (called on validation failed)
     * @return $this
     */
    public function addValidation (Closure $validation, $errorMessage)
    {
        // custom validation start at 'other' index
        if (empty($this->validations))
        {
            $idx = self::$IDX_OTHER;
        }
        else
        {
            // get the last index
            $idx = max(array_keys($this->validations));
            // if the last index is less than the start of 'other' index, start at 'other' index
            if ($idx < self::$IDX_OTHER)
                $idx = self::$IDX_OTHER;
            else // next of the last index
                $idx += 1;
        }

        $this->setValidation($idx, $validation, $errorMessage);

        return $this;
    }

    private function setValidation ($idx, Closure $validation, $errorMessage)
    {
        $this->validations[$idx] = $validation;
        $this->errorMessages[$idx] = $errorMessage;
    }

    /**
     * Run validation.
     * @return bool true if success, false if validation failed
     */
    public function validate ()
    {
        $this->error = null;

        $indexes = array_keys($this->validations);
        sort($indexes);
        foreach ($indexes as $idx)
        {
            if (!$this->validations[$idx]($this->file))
            {
                $errorMessage = $this->errorMessages[$idx];
                if ($errorMessage instanceof Closure)
                    $errorMessage = $errorMessage();

                $this->error = $errorMessage;
                return false;
            }
        }

        return true;
    }

    /**
     * @return string
     */
    public function getError ()
    {
        return $this->error;
    }

    /**
     * @return array
     */
    private static function getMimes ()
    {
        return include('mimes.php');
    }
}
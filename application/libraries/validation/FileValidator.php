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

    /** @var array */
    private $filePath;
    /** @var string */
    private $label;

    private $optional = true;

    /** @var array */
    private $validations = array();
    /** @var array */
    private $errorMessages = array();
    /** @var string */
    private $error;


    /**
     * FileValidator constructor.
     * @param string $filePath
     * @param string $label
     */
    public function __construct ($filePath, $label = 'File')
    {
        $this->filePath = $filePath;
        $this->label = $label;

        $this->error = '';
    }

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function required ($errorMessage = null)
    {
        $this->optional = false;

        if (is_null($errorMessage))
            $errorMessage = '{label} is required';
        $this->errorMessages[self::$IDX_REQUIRED] = $errorMessage;

        return $this;
    }

    /**
     * @param array $types
     * @param string $errorMessage extra placeholders: {types} = comma-separated allowed types
     * @return $this
     */
    public function allowTypes (array $types, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = 'File type must be one of: {types}';

        $errorMessage = $this->formatMessage($errorMessage, [
            '{types}' => implode(', ', $types)
        ]);

        $this->setValidation(self::$IDX_ALLOW_TYPES, function ($filePath) use ($types)
        {
            $mime = mime_content_type($filePath);
            $mimes = self::getMimes();
            foreach ($types as $type)
            {
                if (is_array($mimes[ $type ]))
                {
                    if (in_array($mime, $mimes[ $type ], true))
                        return true;
                }
                else if ($mime === $mimes[ $type ])
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
     * @param null $errorMessage extra placeholders: {size} = size with KB/MB unit
     * @return $this
     */
    public function maxSize ($sizeKB, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = 'Max file size is {size}';

        if ($sizeKB >= 1024)
            $size = sprintf('%s MB', floor($sizeKB * 100.0 / 1024) / 100.0);
        else
            $size = sprintf('%s KB', $sizeKB);

        $errorMessage = $this->formatMessage($errorMessage, [
            '{size}' => $size
        ]);

        $this->setValidation(self::$IDX_SIZE_MAX, function ($filePath) use ($sizeKB)
        {
            $fileSize = filesize($filePath);
            $sizeByte = $sizeKB * 1024;

            return ($fileSize <= $sizeByte);
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
        $this->error = '';

        if (empty($this->filePath))
        {
            if ($this->optional)
            {
                return true;
            }
            else
            {
                $errorMessage = $this->errorMessages[self::$IDX_REQUIRED];
                if ($errorMessage instanceof Closure)
                    $errorMessage = $errorMessage();

                $this->error = $this->formatErrorMessage($errorMessage);
                return false;
            }
        }

        $indexes = array_keys($this->validations);
        sort($indexes);
        foreach ($indexes as $idx)
        {
            if (!$this->validations[$idx]($this->filePath))
            {
                $errorMessage = $this->errorMessages[$idx];
                if ($errorMessage instanceof Closure)
                    $errorMessage = $errorMessage();

                $this->error = $this->formatErrorMessage($errorMessage);
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $errorMessage
     * @return string
     */
    protected function formatErrorMessage ($errorMessage)
    {
        $replacements = [
            '{label}' => $this->label
        ];

        return $this->formatMessage($errorMessage, $replacements);
    }

    private function formatMessage ($message, array $replacements)
    {
        if (empty($message))
            return '';
        else
            return str_replace(array_keys($replacements), array_values($replacements), $message);
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
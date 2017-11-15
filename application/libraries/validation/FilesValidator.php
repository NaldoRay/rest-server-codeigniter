<?php

require_once('FieldValidator.php');
require_once('FileValidator.php');

/**
 * @author Ray Naldo
 */
class FilesValidator extends FieldValidator
{
    /**
     * @param string $name
     * @param string $label (optional)
     * @return FileValidator
     */
    public function field ($name, $label = null)
    {
        if (isset($_FILES[$name]))
            $file = $_FILES[$name];
        else
            $file = null;

        $validator = new FileValidator($file, $label);
        $this->addValidator($name, $validator);

        return $validator;
    }
}
<?php

include_once('FileValidator.php');

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 10:25
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
<?php

require_once('FieldValidator.php');
require_once('ValidatorFactory.php');

/**
 * @author Ray Naldo
 */
class FilesValidator extends FieldValidator
{
    /** @var ValidatorFactory  */
    private $validatorFactory;

    public function __construct (ValidatorFactory $validatorFactory)
    {
        parent::__construct();
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * @param string $name
     * @param string $label (optional)
     * @return FileValidator
     */
    public function field ($name, $label = 'File')
    {
        if (isset($_FILES[$name]))
            $filePath = $_FILES[$name]['tmp_name'];
        else
            $filePath = null;

        $validator = $this->validatorFactory->createFileValidator($filePath, $label);
        $this->addValidator($name, $validator);

        return $validator;
    }
}
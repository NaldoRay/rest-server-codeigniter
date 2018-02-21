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
        {
            $file = $_FILES[$name];
        }
        else
        {
            $file = array(
                'name' => null,
                'type' => null,
                'size' => 0,
                'tmp_name' => null,
                'error' => null
            );
        }

        $validator = $this->validatorFactory->createFileValidator($file, $label);
        $this->addValidator($name, $validator);

        return $validator;
    }
}
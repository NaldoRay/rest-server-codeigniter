<?php

require_once('ValidatorFactory.php');
include_once('FieldValidator.php');
include_once('BatchArrayValidator.php');
include_once('exception/InvalidArrayException.php');
include_once('exception/InvalidBatchArrayException.php');
include_once('exception/InvalidValueException.php');

/**
 * @author Ray Naldo
 */
class InputValidation
{
    /** @var  ValidatorFactory */
    private $validatorFactory;
    /** @var FieldValidator|ValueValidator */
    private $validator;


    public function __construct (ValidatorFactory $validatorFactory = null)
    {
        if (is_null($validatorFactory))
            $validatorFactory = new ValidatorFactory();

        $this->validatorFactory = $validatorFactory;
    }

    public function forArray (array $arr)
    {
        $this->validator = $this->validatorFactory->createArrayValidator($arr);
    }

    public function forBatchArray (array $batchArr)
    {
        $this->validator = $this->validatorFactory->createBatchArrayValidator($batchArr);
    }

    /**
     * @param mixed $value
     * @param string $label
     * @return ValueValidator
     */
    public function forValue ($value, $label = 'Value')
    {
        $this->validator = $this->validatorFactory->createValueValidator($value, $label);
        return $this->validator;
    }

    /**
     * @param string $name
     * @param string|null $label
     * @return ArrayValueValidator
     */
    public function field ($name, $label = 'Value')
    {
        /** @var ArrayValidator|BatchArrayValidator $validator */
        $validator = $this->validator;

        return $validator->field($name, $label);
    }

    public function forFiles ()
    {
        $this->validator = $this->validatorFactory->createFilesValidator();
    }

    /**
     * @param $name
     * @param string|null $label
     * @return FileValidator
     */
    public function file ($name, $label = null)
    {
        /** @var FilesValidator $validator */
        $validator = $this->validator;

        return $validator->field($name, $label);
    }

    /**
     * Run validation.
     * @throws InvalidArrayException
     * @throws InvalidBatchArrayException
     * @throws InvalidValueException
     */
    public function validate ()
    {
        if (!$this->validator->validate())
        {
            if ($this->validator instanceof FieldValidator)
                throw new InvalidArrayException($this->validator->getAllErrors());
            else if ($this->validator instanceof BatchArrayValidator)
                throw new InvalidBatchArrayException($this->validator->getBatchErrors());
            else
                throw new InvalidValueException($this->validator->getError());
        }
    }
}
<?php

require_once('FieldValidator.php');
require_once('ValidatorFactory.php');

/**
 * @author Ray Naldo
 */
class ArrayValidator extends FieldValidator
{
    /** @var ValidatorFactory  */
    private $validatorFactory;
    /** @var array  */
    private $arr;


    public function __construct (ValidatorFactory $validatorFactory, array $arr)
    {
        parent::__construct();

        $this->validatorFactory = $validatorFactory;
        $this->arr = $arr;
    }

    /**
     * @param string $name
     * @param string $label (optional)
     * @return ArrayValueValidator
     */
    public function field ($name, $label = 'Value')
    {
        $validator = $this->validatorFactory->createArrayValueValidator($this->arr, $name, $label);
        $this->addValidator($name, $validator);

        return $validator;
    }

    public function setArray (array $arr)
    {
        $this->arr = $arr;

        $validators = $this->getValidators();
        /** @var ArrayValueValidator $validator */
        foreach ($validators as $field => $validator)
            $validator->setArrayValue($this->arr, $field);
    }
}
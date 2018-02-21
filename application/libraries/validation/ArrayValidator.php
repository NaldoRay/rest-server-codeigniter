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
     * @return ValueValidator
     */
    public function field ($name, $label = 'Value')
    {
        if (isset($this->arr[$name]))
            $value = $this->arr[$name];
        else
            $value = null;

        $validator = $this->validatorFactory->createValueValidator($value, $label);
        $this->addValidator($name, $validator);

        return $validator;
    }

    public function setArray (array $arr)
    {
        $this->arr = $arr;

        $validators = $this->getValidators();
        /** @var ValueValidator $validator */
        foreach ($validators as $field => $validator)
        {
            if (isset($this->arr[$field]))
                $value = $this->arr[$field];
            else
                $value = null;

            $validator->setValue($value);
        }
    }
}
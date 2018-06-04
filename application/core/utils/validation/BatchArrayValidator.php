<?php

require_once('ValidatorFactory.php');

/**
 * @author Ray Naldo
 */
class BatchArrayValidator
{
    /** @var array */
    private $batchArr;
    /** @var ArrayValidator */
    private $arrayValidator;
    /** @var array */
    private $batchErrors;

    public function __construct (ValidatorFactory $validatorFactory, array $batchArr)
    {
        $this->batchArr = $batchArr;
        $this->arrayValidator = $validatorFactory->createArrayValidator(array());

        $this->batchErrors = array();
    }

    /**
     * @param string $name
     * @param string $label (optional)
     * @return ArrayValueValidator
     */
    public function field ($name, $label = 'Value')
    {
        return $this->arrayValidator->field($name, $label);
    }

    public function validate ()
    {
        $this->batchErrors = array();
        $validationSucces = true;

        foreach ($this->batchArr as $arr)
        {
            $this->arrayValidator->setArray($arr);
            if ($this->arrayValidator->validate())
            {
                $this->batchErrors[] = null;
            }
            else
            {
                $this->batchErrors[] = $this->arrayValidator->getAllErrors();
                if ($validationSucces)
                    $validationSucces = false;
            }
        }

        return $validationSucces;
    }

    /**
     * @return array
     */
    public function getBatchErrors ()
    {
        return $this->batchErrors;
    }
}
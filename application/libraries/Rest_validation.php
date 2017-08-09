<?php

include_once('validation/ArrayValidator.php');

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 16:16
 */
class Rest_validation
{
    /** @var ArrayValidator|ValueValidator */
    private $validator;

    public function forArray (array $arr)
    {
        $this->validator = new ArrayValidator($arr);
    }

    /**
     * @param mixed $value
     * @param string $label
     * @return Validation
     */
    public function forValue ($value, $label)
    {
        $this->validator = new ValueValidator($value, $label);
        return $this->validator;
    }

    /**
     * @param string $name
     * @param string $label (optional)
     * @return Validation
     */
    public function field ($name, $label = null)
    {
        /** @var ArrayValidator $validator */
        $validator = $this->validator;

        return $validator->field($name, $label);
    }

    /**
     * Run validation.
     * @throws BadArrayException
     * @throws BadValueException
     */
    public function validate ()
    {
        if (!$this->validator->validate())
        {
            if ($this->validator instanceof ArrayValidator)
                throw new BadArrayException($this->validator->getAllErrors());
            else
                throw new BadValueException($this->validator->getError());
        }
    }
}
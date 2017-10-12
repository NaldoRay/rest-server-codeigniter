<?php

include_once('validation/ArrayValidator.php');

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 16:16
 */
class Rest_validation
{
    /** @var FieldValidator|ValueValidator */
    private $validator;


    /**
     * @param mixed $value
     * @param string $label
     * @return ValueValidator
     */
    public function forValue ($value, $label)
    {
        $this->validator = new ValueValidator($value, $label);
        return $this->validator;
    }

    public function forArray (array $arr)
    {
        $this->validator = new ArrayValidator($arr);
    }

    /**
     * @param string $name
     * @param string|null $label
     * @return ValueValidator
     */
    public function field ($name, $label = null)
    {
        /** @var ArrayValidator $validator */
        $validator = $this->validator;

        return $validator->field($name, $label);
    }

    public function forFiles ()
    {
        $this->validator = new FilesValidator();
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
     * @throws BadArrayException
     * @throws BadValueException
     */
    public function validate ()
    {
        if (!$this->validator->validate())
        {
            if ($this->validator instanceof FieldValidator)
                throw new BadArrayException($this->validator->getAllErrors());
            else
                throw new BadValueException($this->validator->getError());
        }
    }
}
<?php

include_once('validation/ArrayValidator.php');

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 16:16
 */
class Rest_validation
{
    /** @var ArrayValidator */
    private $validator;

    public function forArray (array $arr)
    {
        $this->validator = new ArrayValidator($arr);
    }

    /**
     * @param string $name
     * @param string $label (optional)
     * @return Validation
     */
    public function field ($name, $label = null)
    {
        return $this->validator->field($name, $label);
    }

    /**
     * Run validation.
     * @throws ValidationException if failed
     */
    public function validate ()
    {
        if (!$this->validator->validate())
        {
            throw new ValidationException($this->validator->getAllErrors());
        }
    }
}
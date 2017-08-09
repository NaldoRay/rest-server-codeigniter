<?php

include_once('ValueValidator.php');

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 10:25
 */
class ArrayValidator
{
    private $arr;
    /** @var ValueValidator[] */
    private $validators;
    /** @var array */
    private $errors;

    public function __construct (array $arr)
    {
        $this->arr = $arr;
        $this->validators = array();
        $this->errors = array();
    }

    /**
     * @param string $name
     * @param string $label (optional)
     * @return Validation
     */
    public function field ($name, $label = null)
    {
        if (isset($this->arr[$name]))
            $value = $this->arr[$name];
        else
            $value = null;

        $validator = new ValueValidator($value, $label);
        $this->validators[$name] = $validator;

        return $validator;
    }

    /**
     * Run validation.
     * @return bool true if success, false if validation failed
     */
    public function validate ()
    {
        $this->errors = array();

        foreach ($this->validators as $field => $validator)
        {
            if (!$validator->validate())
            {
                $this->errors[$field] = $validator->getError();
            }
        }

        return empty($this->errors);
    }

    /**
     * @return array
     */
    public function getAllErrors ()
    {
        return $this->errors;
    }
}
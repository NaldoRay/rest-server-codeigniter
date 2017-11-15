<?php

/**
 * @author Ray Naldo
 */
class FieldValidator
{
    /** @var Validation[] */
    private $validators;
    /** @var array */
    private $errors;

    public function __construct ()
    {
        $this->validators = array();
        $this->errors = array();
    }

    protected function addValidator ($field, $validator)
    {
        $this->validators[$field] = $validator;
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
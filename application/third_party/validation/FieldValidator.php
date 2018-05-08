<?php

include_once('Validation.php');

/**
 * @author Ray Naldo
 */
abstract class FieldValidator
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

    /**
     * @return Validation[]
     */
    protected function getValidators ()
    {
        return $this->validators;
    }

    /**
     * @param string $field
     * @return null|Validation
     */
    protected function getValidator ($field)
    {
        return (isset($this->validators[$field]) ? $this->validators[$field] : null);
    }

    /**
     * @param string $field
     * @param Validation $validator
     */
    protected function addValidator ($field, Validation $validator)
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
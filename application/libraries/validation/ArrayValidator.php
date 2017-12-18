<?php

require_once('FieldValidator.php');
require_once('ValueValidator.php');

/**
 * @author Ray Naldo
 */
class ArrayValidator extends FieldValidator
{
    private $arr;

    public function __construct (array $arr)
    {
        parent::__construct();
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

        $validator = new ValueValidator($value, $label);
        $this->addValidator($name, $validator);

        return $validator;
    }
}
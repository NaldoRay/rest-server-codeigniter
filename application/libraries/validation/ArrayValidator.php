<?php

include_once('ValueValidator.php');

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 10:25
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
    public function field ($name, $label = null)
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
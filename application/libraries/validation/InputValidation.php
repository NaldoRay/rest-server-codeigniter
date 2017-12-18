<?php
defined('BASEPATH') OR exit('No direct script access allowed');

include_once('ValueValidator.php');
include_once('ArrayValidator.php');
include_once('FilesValidator.php');

/**
 * @author Ray Naldo
 */
class InputValidation
{
    /** @var FieldValidator|ValueValidator */
    private $validator;
    private $domain = 'Validation';

    /**
     * @param mixed $domain
     */
    public function setDomain ($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getDomain ()
    {
        return $this->domain;
    }


    /**
     * @param mixed $value
     * @param string $label
     * @return ValueValidator
     */
    public function forValue ($value, $label = 'Value')
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
    public function field ($name, $label = 'Value')
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
                throw new BadArrayException($this->validator->getAllErrors(), $this->domain);
            else
                throw new BadValueException($this->validator->getError(), $this->domain);
        }
    }
}
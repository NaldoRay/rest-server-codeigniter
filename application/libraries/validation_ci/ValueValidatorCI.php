<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'libraries/validation/ValueValidator.php');

/**
 * @author Ray Naldo
 */
class ValueValidatorCI extends ValueValidator
{
    public function required ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus diisi';

        return parent::required($errorMessage);
    }

    public function lengthMin ($min, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} minimal {min} karakter';

        return parent::lengthMin($min, $errorMessage);
    }

    public function lengthMax ($max, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} maksimal {max} karakter';

        return parent::lengthMax($max, $errorMessage);
    }

    public function lengthBetween ($min, $max, $errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus antara {min} hingga {max} karakter';

        return parent::lengthBetween($min, $max, $errorMessage);
    }

    public function validEmail ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa e-mail yang valid';

        return parent::validEmail($errorMessage);
    }

    public function validDate ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus valid dengan format [YYYY]-[MM]-[DD]';

        return parent::validDate($errorMessage);
    }

    public function validDateTime ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus valid dengan format [YYYY]-[MM]-[DD]T[hh]:[mm]:[ss][TZD]';

        return parent::validDateTime($errorMessage);
    }

    public function onlyBoolean ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus bernilai true/false';

        return parent::onlyBoolean($errorMessage);
    }

    public function onlyInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa angka bulat';

        return parent::onlyInteger($errorMessage);
    }

    public function onlyPositiveInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa angka bulat positif';

        return parent::onlyPositiveInteger($errorMessage);
    }

    public function onlyFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa angka desimal';

        return parent::onlyFloat($errorMessage);
    }

    public function onlyPositiveFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa angka desimal positif';

        return parent::onlyPositiveFloat($errorMessage);
    }

    public function onlyString ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa teks';

        return parent::onlyString($errorMessage);
    }

    public function onlyNumeric ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa angka';

        return parent::onlyNumeric($errorMessage);
    }

    public function onlyNumericInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa angka bulat';

        return parent::onlyNumericInteger($errorMessage);
    }

    public function onlyPositiveNumericInteger ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa angka bulat positif';

        return parent::onlyPositiveNumericInteger($errorMessage);
    }

    public function onlyNumericFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa (teks) angka desimal';

        return parent::onlyNumericFloat($errorMessage);
    }

    public function onlyPositiveNumericFloat ($errorMessage = null)
    {
        if (is_null($errorMessage))
            $errorMessage = '{label} harus berupa (teks) angka desimal positif';

        return parent::onlyPositiveNumericFloat($errorMessage);
    }

}
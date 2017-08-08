<?php

include_once('validation/ArrayValidator.php');

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 16:16
 */
class Input_validation
{
    /**
     * @param array $arr
     * @return ArrayValidator
     */
    public function forArray (array $arr)
    {
        return new ArrayValidator($arr);
    }
}
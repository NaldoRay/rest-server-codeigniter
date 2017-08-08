<?php

/**
 * Author: RN
 * Date: 8/8/2017
 * Time: 15:24
 */
interface Validation
{
    /**
     * Validasi gagal jika value sama dengan null, '', atau hanya berisi whitespace.
     * @param string $errorMessage custom error message
     * @return $this
     */
    public function required ($errorMessage = null);

    /**
     * @param int $length
     * @param string $errorMessage
     * @return $this
     */
    public function lengthMin ($length, $errorMessage = null);

    /**
     * @param int $length
     * @param string $errorMessage
     * @return $this
     */
    public function lengthMax ($length, $errorMessage = null);

    /**
     * @param int $minLength
     * @param int $maxLength
     * @param string $errorMessage
     * @return $this
     */
    public function lengthBetween ($minLength, $maxLength, $errorMessage = null);

    /**
     * @param string $errorMessage
     * @return $this
     */
    public function validEmail ($errorMessage = null);
}
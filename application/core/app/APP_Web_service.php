<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * @author Ray Naldo
 */
class APP_Web_service extends MY_Web_service
{
    private $inupby = null;


    public function setInupby ($inupby)
    {
        $this->inupby = $inupby;
        return $this;
    }

    public function post ($uri = '', array $params = null)
    {
        if (!is_null($this->inupby))
            $this->setHeader('API-User', $this->inupby);

        return parent::post($uri, $params);
    }

    public function put ($uri = '', array $params = null)
    {
        if (!is_null($this->inupby))
            $this->setHeader('API-User', $this->inupby);

        return parent::put($uri, $params);
    }

    public function patch ($uri = '', array $params = null)
    {
        if (!is_null($this->inupby))
            $this->setHeader('API-User', $this->inupby);

        return parent::patch($uri, $params);
    }
}
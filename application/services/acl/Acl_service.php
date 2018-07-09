<?php

/**
 * Author: Ray Naldo
 * Date: 7/8/2018
 * Time: 01:45
 */
class Acl_service extends APP_Web_service
{
    public function __construct()
    {
        parent::__construct('http://10.210.1.177/ws_acl_new/');
    }

    public function authorizeUser ($id_appl, $username)
    {
        return $this->post("auth",
            [
                'id_appl' => $id_appl,
                'username' => $username
            ]
        );
    }

    public function getUserAuth ($controller, $function, $accessToken)
    {
        return $this->setHeader('Authorization', 'Bearer '.$accessToken)
            ->post('user-auth', [
                'controller' => $controller,
	            'function' => $function
            ]);
    }
}
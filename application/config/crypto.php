<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * User: RN
 * Date: 6/2/2017
 * Time: 10:44
 */

// untuk generate keypair yang digunakan untuk decrypt pesan dari client
$config['crypto_public_key_path'] = '/keypair/public.key';
$config['crypto_private_key_path'] = '/keypair/private.key';
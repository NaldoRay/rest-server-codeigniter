<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/*
 * viewUri dan downloadUri sesuai uri bagian "Files" di config/routes.php.
 * Harus menggunakan trailing slash.
 */
$config['viewUri'] = 'files/';

/*
 * Full path DIREKTORI file asli.
 * Harus menggunakan trailing slash.
 */
$config['sourceDir'] = '/data/uploads/';

/*
 * Full path DIREKTORI file temp untuk view/download.
 * Harus menggunakan trailing slash.
 */
$config['tempDir'] = '/data/tmp_dok/';




/**
 * RN @ 2017
 */
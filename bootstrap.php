<?php
define('APP_ROOT', dirname(__DIR__));

$config = require APP_ROOT . '/config/config.php';
define('APP_DEBUG', !empty($config['debug']));

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
date_default_timezone_set('America/Chicago');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('popsy_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require APP_ROOT . '/includes/functions.php';

<?php

/**
 * Storefront bootstrap for public_html/s/ — routes /s/{slug} into Laravel.
 * Copy: admin/deploy/public_html/s/ → public_html/s/
 */
define('LARAVEL_START', microtime(true));

$adminPublic = dirname(__DIR__).'/admin/public';

if (! is_file($adminPublic.'/index.php')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Salon storefront is not configured (admin/public/index.php missing).';
    exit(1);
}

// LiteSpeed/Apache set SCRIPT_NAME to /s/index.php; Laravel then resolves the path as
// /ak-salon instead of /s/ak-salon. Point SCRIPT_NAME at the real front controller.
$_SERVER['SCRIPT_NAME'] = '/admin/public/index.php';
$_SERVER['SCRIPT_FILENAME'] = $adminPublic.'/index.php';
$_SERVER['PHP_SELF'] = '/admin/public/index.php';

require $adminPublic.'/index.php';

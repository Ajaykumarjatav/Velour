<?php

/**
 * Storefront fallback when root .htaccess does not route /s/* to Laravel.
 * Copy admin/deploy/public_html/s/ → public_html/s/
 */
define('LARAVEL_START', microtime(true));

$adminPublic = dirname(__DIR__).'/admin/public';

if (! is_file($adminPublic.'/index.php')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Salon storefront is not configured (admin/public/index.php missing).';
    exit(1);
}

require $adminPublic.'/index.php';

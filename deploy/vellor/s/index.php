<?php

/**
 * Storefront fallback for staging: public_html/vellor/s/
 * Copy admin/deploy/vellor/s/ → public_html/vellor/s/
 */
define('LARAVEL_START', microtime(true));

$adminPublic = dirname(__DIR__).'/admin/public';

if (! is_file($adminPublic.'/index.php')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Salon storefront is not configured (vellor/admin/public/index.php missing).';
    exit(1);
}

require $adminPublic.'/index.php';

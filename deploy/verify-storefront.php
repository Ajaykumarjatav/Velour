#!/usr/bin/env php
<?php

/**
 * Verify Blade storefront themes and synced assets under public/storefront/.
 *
 * Usage: php deploy/verify-storefront.php
 */

$adminRoot = dirname(__DIR__);
$config = require $adminRoot.'/config/storefront-themes.php';
$themes = array_keys($config['themes'] ?? []);

$engine = env('STOREFRONT_ENGINE', 'blade');
$ok = true;

echo "Storefront engine: {$engine}\n\n";

foreach ($themes as $theme) {
    $view = $adminRoot.'/resources/views/storefront/themes/'.$theme.'/show.blade.php';
    $css = $adminRoot.'/public/storefront/'.$theme.'/theme.css';
    $assetsDir = $adminRoot.'/public/storefront/'.$theme.'/assets';

    if (! is_file($view)) {
        fwrite(STDERR, "[{$theme}] missing Blade view: resources/views/storefront/themes/{$theme}/show.blade.php\n");
        $ok = false;
    }

    if (! is_file($css)) {
        fwrite(STDERR, "[{$theme}] missing theme.css — run: php scripts/sync-storefront-assets.php\n");
        $ok = false;
    }

    if (! is_dir($assetsDir)) {
        fwrite(STDERR, "[{$theme}] missing public/storefront/{$theme}/assets/\n");
        $ok = false;
        continue;
    }

    $assetCount = count(array_filter(scandir($assetsDir) ?: [], fn ($f) => ! in_array($f, ['.', '..'], true)));

    if ($ok) {
        echo "[{$theme}] OK — Blade view, theme.css, {$assetCount} asset(s)\n";
    }
}

if (! $ok) {
    exit(1);
}

echo "\nBlade storefront looks good.\n";

function env(string $key, mixed $default = null): mixed
{
    $adminRoot = dirname(__DIR__);
    $envFile = $adminRoot.'/.env';
    if (! is_file($envFile)) {
        return $default;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (! str_contains($line, '=') || str_starts_with(trim($line), '#')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        if (trim($k) === $key) {
            return trim($v, " \t\"'");
        }
    }

    return $default;
}

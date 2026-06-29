#!/usr/bin/env php
<?php

/**
 * Verify salon storefront theme builds exist under public/website/.
 * Run after: cd salon-website && npm run build:all
 *
 * Usage: php deploy/verify-storefront.php
 */

$publicWebsite = dirname(__DIR__).'/public/website';

if (! is_dir($publicWebsite)) {
    fwrite(STDERR, "Missing directory: {$publicWebsite}\n");
    fwrite(STDERR, "Run: cd salon-website && npm run build:all\n");
    exit(1);
}

$themesDir = dirname(__DIR__).'/salon-website/themes';
$themes = is_dir($themesDir)
    ? array_values(array_filter(scandir($themesDir) ?: [], fn ($d) => $d !== '.' && $d !== '..' && is_dir($themesDir.'/'.$d)))
    : [];

if ($themes === []) {
    fwrite(STDERR, "No themes in salon-website/themes/. Run npm run build:all\n");
    exit(1);
}

$ok = true;

foreach ($themes as $theme) {
    $index = $publicWebsite.'/'.$theme.'/index.html';
    $assetsDir = $publicWebsite.'/'.$theme.'/assets';

    if (! is_file($index)) {
        fwrite(STDERR, "[{$theme}] missing index.html\n");
        $ok = false;
        continue;
    }

    if (! is_dir($assetsDir)) {
        fwrite(STDERR, "[{$theme}] missing assets/\n");
        $ok = false;
        continue;
    }

    $assets = array_values(array_filter(scandir($assetsDir) ?: [], fn ($f) => ! in_array($f, ['.', '..'], true)));
    $js = array_filter($assets, fn ($f) => str_ends_with($f, '.js'));
    $css = array_filter($assets, fn ($f) => str_ends_with($f, '.css'));

    if ($js === [] || $css === []) {
        fwrite(STDERR, "[{$theme}] expected at least one .js and .css in assets/\n");
        $ok = false;
        continue;
    }

    echo "[{$theme}] OK — ".count($assets)." asset file(s)\n";
}

if (! $ok) {
    exit(1);
}

echo "\nStorefront builds look good. Commit public/website/ and deploy.\n";
echo "Live site: also copy deploy/public_html/.htaccess and deploy/public_html/s/ to document root.\n";

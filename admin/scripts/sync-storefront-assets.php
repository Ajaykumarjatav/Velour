#!/usr/bin/env php
<?php

/**
 * Sync salon storefront static assets + CSS from React build output to public/storefront/.
 *
 * Usage: php scripts/sync-storefront-assets.php
 */

$root = dirname(__DIR__);
$themesDir = $root.'/salon-website/themes';
$publicWebsite = $root.'/public/website';
$publicStorefront = $root.'/public/storefront';

if (! is_dir($themesDir)) {
    fwrite(STDERR, "Missing salon-website/themes/\n");
    exit(1);
}

$themes = array_values(array_filter(scandir($themesDir) ?: [], fn ($d) => $d !== '.' && $d !== '..' && is_dir($themesDir.'/'.$d)));

function copyDir(string $from, string $to): int
{
    if (! is_dir($from)) {
        return 0;
    }
    if (! is_dir($to)) {
        mkdir($to, 0755, true);
    }
    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $target = $to.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
        if ($item->isDir()) {
            if (! is_dir($target)) {
                mkdir($target, 0755, true);
            }
            continue;
        }
        $parent = dirname($target);
        if (! is_dir($parent)) {
            mkdir($parent, 0755, true);
        }
        copy($item->getPathname(), $target);
        $count++;
    }

    return $count;
}

$ok = true;

foreach ($themes as $theme) {
    $dest = $publicStorefront.'/'.$theme;
    $assetsFrom = $themesDir.'/'.$theme.'/public/assets';
    $assetsTo = $dest.'/assets';

    $copied = copyDir($assetsFrom, $assetsTo);
    echo "[{$theme}] assets: {$copied} file(s)\n";

    $cssGlob = glob($publicWebsite.'/'.$theme.'/assets/index-*.css') ?: [];
    if ($cssGlob !== []) {
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        copy($cssGlob[0], $dest.'/theme.css');
        echo "[{$theme}] theme.css copied from ".basename($cssGlob[0])."\n";
    } else {
        fwrite(STDERR, "[{$theme}] WARN: no built CSS in public/website/{$theme}/assets/\n");
        $ok = false;
    }

    $view = $root.'/resources/views/storefront/themes/'.$theme.'/show.blade.php';
    if (! is_file($view)) {
        fwrite(STDERR, "[{$theme}] WARN: Blade view missing ({$view})\n");
    }
}

if (! $ok) {
    fwrite(STDERR, "\nSome themes missing CSS. Run: cd salon-website && npm run build:all\n");
    exit(1);
}

echo "\nStorefront assets synced to public/storefront/\n";

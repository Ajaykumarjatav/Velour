#!/usr/bin/env php
<?php

/**
 * Publish easygrox.com root routing files from admin/deploy/public_html/
 * to the parent public_html directory (one level above admin/).
 *
 * Run on server from admin folder:
 *   cd domains/easygrox.com/public_html/admin
 *   php scripts/publish-easygrox-root-routing.php
 *
 * This enables /s/{slug} → Laravel Blade storefront (required once per server).
 */

$adminRoot = dirname(__DIR__);
$source = $adminRoot.'/deploy/public_html';
$target = dirname($adminRoot);

if (! is_dir($source)) {
    fwrite(STDERR, "Missing: {$source}\n");
    exit(1);
}

$copies = [
    '.htaccess' => '.htaccess',
    's/.htaccess' => 's/.htaccess',
    's/index.php' => 's/index.php',
];

foreach ($copies as $relSrc => $relDst) {
    $from = $source.'/'.$relSrc;
    $to = $target.'/'.$relDst;

    if (! is_file($from)) {
        fwrite(STDERR, "Skip missing source: {$relSrc}\n");
        continue;
    }

    $dir = dirname($to);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    if (copy($from, $to)) {
        echo "Published {$relDst}\n";
    } else {
        fwrite(STDERR, "Failed to copy {$relSrc} → {$relDst}\n");
        exit(1);
    }
}

echo "\nDone. Test: https://easygrox.com/s/{salon-slug}\n";

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
 * Enables https://easygrox.com/s/{slug} → Laravel Blade storefront.
 */

$adminRoot = dirname(__DIR__);
$source = $adminRoot.'/deploy/public_html';
$target = dirname($adminRoot);

if (! is_dir($source)) {
    fwrite(STDERR, "Missing: {$source}\n");
    exit(1);
}

echo "Admin:  {$adminRoot}\n";
echo "Target: {$target}\n\n";

$copies = [
    '.htaccess'     => '.htaccess',
    's/.htaccess'   => 's/.htaccess',
    's/index.php'   => 's/index.php',
];

$ok = true;

foreach ($copies as $relSrc => $relDst) {
    $from = $source.'/'.$relSrc;
    $to = $target.'/'.$relDst;

    if (! is_file($from)) {
        fwrite(STDERR, "[FAIL] Missing source: {$relSrc}\n");
        $ok = false;
        continue;
    }

    $dir = dirname($to);
    if (! is_dir($dir) && ! mkdir($dir, 0755, true)) {
        fwrite(STDERR, "[FAIL] Cannot create directory: {$dir}\n");
        $ok = false;
        continue;
    }

    if (! copy($from, $to)) {
        fwrite(STDERR, "[FAIL] Copy {$relSrc} → {$relDst}\n");
        $ok = false;
        continue;
    }

    echo "[OK] Published {$relDst}\n";
}

if (! $ok) {
    exit(1);
}

// Verify root .htaccess contains /s/ rule
$htaccess = (string) file_get_contents($target.'/.htaccess');
if (! str_contains($htaccess, 'RewriteRule ^s(')) {
    fwrite(STDERR, "[WARN] Root .htaccess may be missing /s/ rewrite rule.\n");
}

if (! is_file($target.'/s/index.php')) {
    fwrite(STDERR, "[FAIL] s/index.php missing after publish.\n");
    exit(1);
}

echo "\nRouting published successfully.\n";
echo "Test: curl -I https://easygrox.com/s/ak-salon\n";
echo "Expected: HTTP 200 (not 404)\n";

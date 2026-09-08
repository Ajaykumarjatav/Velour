<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "gd loaded: " . (extension_loaded('gd') ? 'yes' : 'no') . PHP_EOL;
print_r(gd_info());

$p = __DIR__ . '/../public/images/easygrox-logo-dark.png';
echo "path: {$p}" . PHP_EOL;
echo "exists: " . (file_exists($p) ? 'yes' : 'no') . PHP_EOL;

$im = @imagecreatefrompng($p);
if (! $im) {
    echo "imagecreatefrompng failed\n";
    $err = error_get_last();
    print_r($err);
    // try icon
    $p2 = __DIR__ . '/../public/images/easygrox-icon.png';
    $im = @imagecreatefrompng($p2);
    echo "icon: " . ($im ? 'ok' : 'fail') . PHP_EOL;
    if ($im) {
        $p = $p2;
    }
}

if (! $im) {
    exit(1);
}

$w = imagesx($im);
$h = imagesy($im);
$out = imagecreatetruecolor($w, $h);
$white = imagecolorallocate($out, 255, 255, 255);
imagefilledrectangle($out, 0, 0, $w, $h, $white);
imagealphablending($out, true);
imagecopy($out, $im, 0, 0, 0, 0, $w, $h);
$dest = __DIR__ . '/../public/images/easygrox-logo-invoice.jpg';
imagejpeg($out, $dest, 92);
imagedestroy($im);
imagedestroy($out);
echo "wrote {$dest} (" . filesize($dest) . " bytes) from {$p}\n";

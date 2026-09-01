<?php
/**
 * Generates docs/reference/CODE_REFERENCE.md from app/ PHP sources.
 * Run: php scripts/generate-code-reference.php
 */
$root = dirname(__DIR__);
$appDir = $root . '/app';
$outFile = $root . '/docs/reference/CODE_REFERENCE.md';

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($appDir, FilesystemIterator::SKIP_DOTS)
);

$byDir = [];
foreach ($files as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getPathname();
    $rel = str_replace('\\', '/', substr($path, strlen($root) + 1));
    $code = file_get_contents($path);
    if (!preg_match('/^namespace\s+([^;]+);/m', $code, $ns)) {
        continue;
    }
    $namespace = trim($ns[1]);
    $short = basename($path, '.php');
    $fqcn = $namespace . '\\' . $short;

    preg_match_all('/^\s*(public|protected)\s+function\s+(\w+)\s*\(/m', $code, $m);
    $methods = $m[2] ?? [];
    if ($methods === []) {
        continue;
    }

    $dirKey = dirname($rel);
    $byDir[$dirKey][$rel] = [
        'fqcn' => $fqcn,
        'methods' => $methods,
    ];
}

ksort($byDir);

$md = "# Code reference (generated)\n\n";
$md .= "Auto-generated listing of **public** and **protected** methods under `app/`.\n\n";
$md .= "**Regenerate:** `php scripts/generate-code-reference.php`\n\n";
$md .= '**Generated:** ' . date('Y-m-d H:i:s T') . "\n\n";
$md .= "---\n\n";

$totalFiles = 0;
$totalMethods = 0;
foreach ($byDir as $dir => $classes) {
    $md .= "## `{$dir}/`\n\n";
    ksort($classes);
    foreach ($classes as $rel => $info) {
        $totalFiles++;
        $totalMethods += count($info['methods']);
        $md .= "### `{$info['fqcn']}`\n\n";
        $md .= "File: [`{$rel}`](../../{$rel})\n\n";
        $md .= "| Method |\n|--------|\n";
        foreach ($info['methods'] as $method) {
            $md .= "| `{$method}()` |\n";
        }
        $md .= "\n";
    }
}

$md .= "---\n\n*{$totalFiles} classes, {$totalMethods} methods indexed.*\n";

if (!is_dir(dirname($outFile))) {
    mkdir(dirname($outFile), 0755, true);
}
file_put_contents($outFile, $md);
echo "Wrote {$outFile} ({$totalFiles} classes, {$totalMethods} methods)\n";

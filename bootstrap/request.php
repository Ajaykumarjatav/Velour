<?php

/**
 * Normalize REQUEST_URI when the app runs in a subdirectory (e.g. /vellor)
 * or behind a root .htaccess that rewrites to /public without changing the
 * browser URL.
 */
function vellor_normalize_request_uri(): void
{
    if (PHP_SAPI === 'cli' || ! isset($_SERVER['REQUEST_URI'])) {
        return;
    }

    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';
    $query = parse_url($uri, PHP_URL_QUERY);
    $suffix = $query !== null && $query !== '' ? '?'.$query : '';

    $bases = [];

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if ($script !== '') {
        $dir = rtrim(dirname($script), '/');
        if ($dir !== '' && $dir !== '.') {
            $bases[] = $dir;
            if (str_ends_with($dir, '/public')) {
                $bases[] = substr($dir, 0, -strlen('/public'));
            }
        }
    }

    $envFile = dirname(__DIR__).'/.env';
    if (is_readable($envFile)) {
        $env = file_get_contents($envFile);
        if (preg_match('/^APP_URL=(.+)$/m', $env, $matches)) {
            $appUrl = trim($matches[1], " \t\n\r\0\x0B\"'");
            $appPath = parse_url($appUrl, PHP_URL_PATH) ?: '';
            $appPath = rtrim($appPath, '/');
            if ($appPath !== '') {
                $bases[] = $appPath;
                $publicPrefix = preg_replace('#/admin$#', '', $appPath);
                $publicPrefix = rtrim((string) $publicPrefix, '/');
                if ($publicPrefix !== '' && $publicPrefix !== $appPath) {
                    $bases[] = $publicPrefix;
                }
            }
        }
    }

    $bases = array_values(array_unique(array_filter($bases)));
    usort($bases, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

    foreach ($bases as $base) {
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
            break;
        }
    }

    if (str_starts_with($path, '/public/')) {
        $path = substr($path, 7) ?: '/';
    } elseif ($path === '/public') {
        $path = '/';
    }

    $_SERVER['REQUEST_URI'] = $path.$suffix;
}

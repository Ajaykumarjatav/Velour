<?php

/**
 * Normalize REQUEST_URI when EasyGrox runs in a subdirectory
 * or behind a root .htaccess that rewrites to /public without changing the
 * browser URL.
 */
function easygrox_normalize_request_uri(): void
{
    if (PHP_SAPI === 'cli' || ! isset($_SERVER['REQUEST_URI'])) {
        return;
    }

    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH) ?? '/';
    $query = parse_url($uri, PHP_URL_QUERY);
    $suffix = $query !== null && $query !== '' ? '?'.$query : '';

    // Root .htaccess on easygrox.com rewrites /s/{slug} → admin/public/index.php.
    // LiteSpeed often leaves REQUEST_URI as /admin/public/index.php; recover /s/... from redirect env.
    $storefrontPath = easygrox_storefront_redirect_path();
    if ($storefrontPath !== null) {
        $_SERVER['REQUEST_URI'] = $storefrontPath.$suffix;

        return;
    }

    // Already the public storefront path — do not strip /s via admin base prefixes.
    if (preg_match('#^/s/.+#', $path)) {
        $_SERVER['REQUEST_URI'] = $path.$suffix;

        return;
    }

    $bases = [];

    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    // Storefront fallback (public_html/s/index.php) must not strip /s from the URI.
    $isStorefrontBootstrap = (bool) preg_match('#/s/index\.php$#', $script);

    if ($script !== '' && ! $isStorefrontBootstrap) {
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

function easygrox_storefront_redirect_path(): ?string
{
    foreach (['REDIRECT_URL', 'REDIRECT_URI'] as $key) {
        $raw = $_SERVER[$key] ?? '';
        if ($raw === '') {
            continue;
        }

        $candidate = parse_url($raw, PHP_URL_PATH) ?? '';
        if (preg_match('#^/s/.+#', $candidate)) {
            return $candidate;
        }
    }

    return null;
}

/** @deprecated Use easygrox_normalize_request_uri() */
function vellor_normalize_request_uri(): void
{
    easygrox_normalize_request_uri();
}

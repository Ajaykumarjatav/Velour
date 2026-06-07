<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Consistent public-disk paths and URLs (works with APP_URL / ASSET_URL subfolder installs).
 */
final class PublicStorage
{
    public static function normalizePath(?string $path): ?string
    {
        $path = trim(str_replace('\\', '/', (string) $path));
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            if (preg_match('#/storage/([^?#]+)#i', $path, $matches)) {
                return self::normalizePath($matches[1]);
            }

            return null;
        }

        $path = ltrim($path, '/');
        foreach (['storage/', 'public/storage/', 'public/'] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = (string) Str::after($path, $prefix);
            }
        }

        return $path !== '' ? $path : null;
    }

    /**
     * @return list<string>
     */
    public static function pathCandidates(?string $path): array
    {
        $normalized = self::normalizePath($path);
        if ($normalized === null) {
            return [];
        }

        $candidates = [$normalized];

        if (Str::contains($normalized, '/')) {
            $candidates[] = basename($normalized);
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    public static function exists(?string $path): bool
    {
        foreach (self::pathCandidates($path) as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return true;
            }
        }

        return false;
    }

    public static function resolveExistingPath(?string $path): ?string
    {
        foreach (self::pathCandidates($path) as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public static function url(?string $path): ?string
    {
        $resolved = self::resolveExistingPath($path);
        if ($resolved === null) {
            return null;
        }

        return asset('storage/' . $resolved);
    }

    public static function delete(?string $path): void
    {
        $resolved = self::resolveExistingPath($path);
        if ($resolved !== null) {
            Storage::disk('public')->delete($resolved);
        }
    }
}

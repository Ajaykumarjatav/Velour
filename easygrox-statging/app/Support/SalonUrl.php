<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\Staff;
use App\Models\User;
use App\Scopes\TenantScope;
use Illuminate\Support\Str;

/**
 * Path segment for salon panel URLs: /{store}/dashboard
 */
final class SalonUrl
{
    /** @var list<string> */
    public const RESERVED = [
        'admin',
        'api',
        'account',
        'billing',
        'onboarding',
        'help',
        'legal',
        'book',
        'login',
        'register',
        'logout',
        'password',
        'forgot-password',
        'reset-password',
        'email',
        'two-factor',
        'settings',
        'up',
        'sanctum',
        'storage',
        'livewire',
        'horizon',
        'telescope',
    ];

    public static function key(Salon $salon): string
    {
        $key = strtolower(trim((string) ($salon->subdomain ?: $salon->slug ?: '')));
        if ($key === '' || in_array($key, self::RESERVED, true)) {
            return 's'.$salon->id;
        }

        return $key;
    }

    public static function findByKey(string $key): ?Salon
    {
        $key = strtolower(trim($key));
        if ($key === '' || in_array($key, self::RESERVED, true)) {
            return null;
        }

        return Salon::query()->withoutGlobalScopes()
            ->where(function ($q) use ($key) {
                $q->where('subdomain', $key)->orWhere('slug', $key);
            })
            ->first();
    }

    public static function userCanAccess(User $user, Salon $salon): bool
    {
        if ($user->isSuperAdmin() && AdminStoreBrowse::isActive() && AdminStoreBrowse::salonId() === (int) $salon->id) {
            return true;
        }

        if ($user->salons()->whereKey($salon->id)->exists()) {
            return true;
        }

        return Staff::withoutGlobalScope(TenantScope::class)
            ->where('user_id', $user->id)
            ->where('salon_id', $salon->id)
            ->exists();
    }

    /** Generate a named route, injecting {store} when the user has a salon. */
    public static function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        if (! array_key_exists('store', $parameters)) {
            $user = auth()->user();
            if ($user) {
                $key = self::keyForUser($user);
                if ($key) {
                    $parameters['store'] = $key;
                }
            }
        }

        return route($name, $parameters, $absolute);
    }

    /** Safe dashboard URL when {store} may be missing from the current request. */
    public static function dashboardUrl(?User $user = null): string
    {
        $user ??= auth()->user();
        if (! $user) {
            return route('login');
        }

        $key = self::keyForUser($user);
        if ($key) {
            return route('dashboard', ['store' => $key]);
        }

        if ($user->isSuperAdmin()) {
            return route('admin.dashboard');
        }

        return url('/');
    }

    public static function keyForUser(User $user): ?string
    {
        if ($user->isSuperAdmin() && AdminStoreBrowse::isActive()) {
            $salon = Salon::query()->withoutGlobalScopes()->find(AdminStoreBrowse::salonId());

            return $salon ? self::key($salon) : null;
        }

        $activeSalonId = (int) session('active_salon_id', 0);
        if ($user->salons()->exists()) {
            $salon = $activeSalonId > 0
                ? $user->salons()->whereKey($activeSalonId)->first()
                : null;
            $salon ??= $user->salons()->orderBy('id')->first();

            return $salon ? self::key($salon) : null;
        }

        $staff = null;
        if ($activeSalonId > 0) {
            $staff = Staff::withoutGlobalScope(TenantScope::class)
                ->where('user_id', $user->id)
                ->where('salon_id', $activeSalonId)
                ->first();
        }
        $staff ??= Staff::withoutGlobalScope(TenantScope::class)
            ->where('user_id', $user->id)
            ->orderBy('id')
            ->first();

        if (! $staff) {
            return null;
        }

        $salon = Salon::query()->withoutGlobalScopes()->find($staff->salon_id);

        return $salon ? self::key($salon) : null;
    }

    public static function reservedPattern(): string
    {
        $alts = implode('|', array_map(fn (string $r) => preg_quote($r, '/'), self::RESERVED));

        return '^(?!'.$alts.')[a-z0-9]+(?:-[a-z0-9]+)*$';
    }

    public static function ensureKey(Salon $salon): string
    {
        $key = self::key($salon);
        if (! $salon->subdomain) {
            $salon->forceFill(['subdomain' => $key])->saveQuietly();
        }

        return $key;
    }

    public static function suggestFromName(string $name): string
    {
        $slug = Str::slug($name) ?: 'salon';
        // Prefer short first token when name is like "ak salon"
        $parts = preg_split('/[\s\-]+/', strtolower(trim($name))) ?: [];
        if (count($parts) >= 2 && strlen($parts[0]) >= 2 && strlen($parts[0]) <= 12 && preg_match('/^[a-z0-9]+$/', $parts[0])) {
            $candidate = $parts[0];
            if (! in_array($candidate, self::RESERVED, true)) {
                return $candidate;
            }
        }

        return $slug;
    }
}

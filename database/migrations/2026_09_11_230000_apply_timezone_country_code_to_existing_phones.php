<?php

use App\Support\PhoneCountry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Existing phones get the country code that matches the selected timezone.
     * Run on every environment (including live) via php artisan migrate.
     */
    public function up(): void
    {
        $salonTimezoneById = [];
        $salonTimezoneByOwner = [];
        $salonTimezoneByStaffUser = [];

        if (Schema::hasTable('salons') && Schema::hasColumn('salons', 'timezone')) {
            DB::table('salons')->orderBy('id')->select('id', 'owner_id', 'timezone')->each(function ($salon) use (&$salonTimezoneById, &$salonTimezoneByOwner) {
                $tz = trim((string) ($salon->timezone ?? ''));
                $salonTimezoneById[(int) $salon->id] = $tz !== '' ? $tz : 'Asia/Kolkata';
                $ownerId = (int) ($salon->owner_id ?? 0);
                if ($ownerId > 0 && ! isset($salonTimezoneByOwner[$ownerId])) {
                    $salonTimezoneByOwner[$ownerId] = $salonTimezoneById[(int) $salon->id];
                }
            });
        }

        if (Schema::hasTable('staff') && Schema::hasColumn('staff', 'user_id')) {
            DB::table('staff')
                ->whereNotNull('user_id')
                ->orderBy('id')
                ->select('user_id', 'salon_id')
                ->each(function ($row) use (&$salonTimezoneByStaffUser, $salonTimezoneById) {
                    $userId = (int) $row->user_id;
                    $salonId = (int) $row->salon_id;
                    if ($userId > 0 && ! isset($salonTimezoneByStaffUser[$userId])) {
                        $salonTimezoneByStaffUser[$userId] = $salonTimezoneById[$salonId] ?? 'Asia/Kolkata';
                    }
                });
        }

        if (Schema::hasTable('salons') && Schema::hasColumn('salons', 'phone')) {
            DB::table('salons')->orderBy('id')->select('id', 'timezone', 'phone', 'whatsapp_number', 'whatsapp_same_as_phone', 'social_links')->each(function ($salon) {
                $tz = trim((string) ($salon->timezone ?? '')) ?: 'Asia/Kolkata';
                $next = [
                    'phone' => PhoneCountry::applyTimezoneCountry($salon->phone, $tz),
                ];
                if (Schema::hasColumn('salons', 'whatsapp_number')) {
                    $next['whatsapp_number'] = PhoneCountry::applyTimezoneCountry($salon->whatsapp_number, $tz);
                }

                $changed = ((string) ($next['phone'] ?? '')) !== ((string) ($salon->phone ?? ''))
                    || ((string) ($next['whatsapp_number'] ?? '')) !== ((string) ($salon->whatsapp_number ?? ''));

                if (Schema::hasColumn('salons', 'social_links')) {
                    $links = is_string($salon->social_links) ? json_decode($salon->social_links, true) : $salon->social_links;
                    $links = is_array($links) ? $links : [];
                    $sameAsPhone = ! isset($salon->whatsapp_same_as_phone) || (int) $salon->whatsapp_same_as_phone === 1;
                    $waSource = $sameAsPhone ? ($next['phone'] ?? null) : ($next['whatsapp_number'] ?? null);
                    $waDigits = preg_replace('/\D+/', '', (string) $waSource) ?? '';
                    if ($waDigits !== '') {
                        $links['whatsapp'] = 'https://wa.me/'.$waDigits;
                    }
                    $encoded = json_encode($links);
                    if ($encoded !== (is_string($salon->social_links) ? $salon->social_links : json_encode($salon->social_links))) {
                        $next['social_links'] = $encoded;
                        $changed = true;
                    }
                }

                if ($changed) {
                    $next['updated_at'] = now();
                    DB::table('salons')->where('id', $salon->id)->update($next);
                }
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone')) {
            DB::table('users')->orderBy('id')->select('id', 'phone', 'timezone')->each(function ($user) use ($salonTimezoneByOwner, $salonTimezoneByStaffUser) {
                $tz = trim((string) ($user->timezone ?? ''));
                if ($tz === '') {
                    $tz = $salonTimezoneByOwner[(int) $user->id]
                        ?? $salonTimezoneByStaffUser[(int) $user->id]
                        ?? 'Asia/Kolkata';
                }
                $next = PhoneCountry::applyTimezoneCountry($user->phone, $tz);
                if ((string) ($next ?? '') !== (string) ($user->phone ?? '')) {
                    DB::table('users')->where('id', $user->id)->update([
                        'phone' => $next,
                        'updated_at' => now(),
                    ]);
                }
            });
        }

        if (Schema::hasTable('staff') && Schema::hasColumn('staff', 'phone')) {
            DB::table('staff')->orderBy('id')->select('id', 'salon_id', 'phone')->each(function ($staff) use ($salonTimezoneById) {
                $tz = $salonTimezoneById[(int) $staff->salon_id] ?? 'Asia/Kolkata';
                $next = PhoneCountry::applyTimezoneCountry($staff->phone, $tz);
                if ((string) ($next ?? '') !== (string) ($staff->phone ?? '')) {
                    DB::table('staff')->where('id', $staff->id)->update([
                        'phone' => $next,
                        'updated_at' => now(),
                    ]);
                }
            });
        }

        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'phone')) {
            DB::table('clients')->orderBy('id')->select('id', 'salon_id', 'phone')->each(function ($client) use ($salonTimezoneById) {
                $tz = $salonTimezoneById[(int) $client->salon_id] ?? 'Asia/Kolkata';
                $next = PhoneCountry::applyTimezoneCountry($client->phone, $tz);
                if ((string) ($next ?? '') !== (string) ($client->phone ?? '')) {
                    DB::table('clients')->where('id', $client->id)->update([
                        'phone' => $next,
                        'updated_at' => now(),
                    ]);
                }
            });
        }

        if (Schema::hasTable('marketplace_customers') && Schema::hasColumn('marketplace_customers', 'phone')) {
            DB::table('marketplace_customers')->orderBy('id')->select('id', 'phone')->each(function ($row) {
                $next = PhoneCountry::applyTimezoneCountry($row->phone, 'Asia/Kolkata');
                if ((string) ($next ?? '') !== (string) ($row->phone ?? '')) {
                    DB::table('marketplace_customers')->where('id', $row->id)->update([
                        'phone' => $next,
                        'updated_at' => now(),
                    ]);
                }
            });
        }
    }

    public function down(): void
    {
        // Irreversible data backfill.
    }
};

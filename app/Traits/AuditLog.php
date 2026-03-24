<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * AuditLog (model trait)
 *
 * Attaches automatic activity logging to any Eloquent model.
 * Writes to the activity_log table via spatie/laravel-activitylog.
 *
 * What is logged:
 *   created  → all fillable attributes (initial values)
 *   updated  → only dirty attributes (before / after comparison)
 *   deleted  → tombstone entry (ID, name/email if present, salon_id)
 *   restored → for SoftDelete models
 *
 * Properties captured with every event:
 *   user_id, user_email, ip_address, user_agent, salon_id, request_id
 *
 * Usage:
 *   class Client extends Model {
 *       use AuditLog;
 *       protected array $auditExclude = ['updated_at']; // optional
 *   }
 *
 * Prevent logging on a specific operation:
 *   Client::withoutAuditLog(fn() => $client->update([...]));
 */
trait AuditLog
{
    /** Fields to exclude from change logs (passwords, tokens, etc.). */
    protected array $auditExclude = [
        'password', 'remember_token', 'two_factor_secret',
        'two_factor_recovery_codes', 'two_factor_code',
        'stripe_id', 'pm_type', 'pm_last_four',
        'updated_at',
    ];

    private static bool $auditLogDisabled = false;

    public static function bootAuditLog(): void
    {
        static::created(function (Model $model) {
            if (! static::$auditLogDisabled) {
                static::writeLog('created', $model, [], $model->getAuditableAttributes($model->getAttributes()));
            }
        });

        static::updated(function (Model $model) {
            if (! static::$auditLogDisabled) {
                $dirty = $model->getDirty();
                $clean = $model->getAuditableAttributes($dirty);

                if (empty($clean)) return; // No meaningful changes to log

                $old = [];
                foreach ($clean as $key => $newVal) {
                    $old[$key] = $model->getOriginal($key);
                }

                static::writeLog('updated', $model, $old, $clean);
            }
        });

        static::deleted(function (Model $model) {
            if (! static::$auditLogDisabled) {
                static::writeLog('deleted', $model, [
                    'id'      => $model->getKey(),
                    'name'    => $model->name ?? $model->email ?? null,
                    'salon_id'=> $model->salon_id ?? null,
                ]);
            }
        });

        // Soft delete restore
        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                if (! static::$auditLogDisabled) {
                    static::writeLog('restored', $model);
                }
            });
        }
    }

    /**
     * Execute a callback without triggering audit logging.
     * Used for system updates (sync jobs, migrations) that shouldn't pollute the log.
     */
    public static function withoutAuditLog(callable $callback): mixed
    {
        static::$auditLogDisabled = true;
        try {
            return $callback();
        } finally {
            static::$auditLogDisabled = false;
        }
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private static function writeLog(string $event, Model $model, array $old = [], array $new = []): void
    {
        try {
            $activity = \Spatie\Activitylog\Facades\Activity::performedOn($model)
                ->causedBy(Auth::id())
                ->withProperties([
                    'old'        => $old ?: null,
                    'new'        => $new ?: null,
                    'ip'         => Request::ip(),
                    'user_agent' => substr(Request::userAgent() ?? '', 0, 255),
                    'salon_id'   => $model->salon_id ?? null,
                    'request_id' => Request::header('X-Request-ID'),
                ]);

            // Use subject_type/subject_id for log description
            $activity->log($event);

        } catch (\Throwable $e) {
            // Never let audit logging break the main request
            Log::warning('[AuditLog trait] Failed to write activity', [
                'model' => get_class($model),
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getAuditableAttributes(array $attrs): array
    {
        $exclude = array_merge(
            $this->auditExclude ?? [],
            ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes']
        );

        return array_filter(
            $attrs,
            fn ($key) => ! in_array($key, $exclude, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}

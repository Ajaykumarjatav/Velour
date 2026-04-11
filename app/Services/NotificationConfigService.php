<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Salon;

/**
 * Salon notification rules: definitions, merged config (defaults + saved JSON + legacy keys), and template rendering.
 */
class NotificationConfigService
{
    public const CONFIG_KEY = 'notification_config';

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            'client_booking_confirmation_email' => [
                'label'       => 'Client — booking confirmation (email)',
                'description' => 'Sent to the client immediately after an online booking is confirmed.',
                'timing'      => 'instant',
                'channels'    => ['email'],
                'default_enabled' => true,
                'default_offset_hours' => null,
                'variables'   => ['client_first_name', 'client_last_name', 'salon_name', 'appointment_date', 'appointment_time', 'staff_name', 'service_names', 'reference', 'salon_phone', 'salon_email'],
                'default_templates' => [
                    'email_subject' => 'Your appointment at {{salon_name}}',
                    'email_body'    => "Hi {{client_first_name}},\n\nYou're booked for {{appointment_date}} at {{appointment_time}} with {{staff_name}}.\nServices: {{service_names}}\nReference: {{reference}}\n\n{{salon_name}}\n{{salon_phone}}",
                ],
            ],
            'client_appointment_reminder_email' => [
                'label'       => 'Client — reminder (email)',
                'description' => 'Scheduled email to the client before their appointment.',
                'timing'      => 'scheduled',
                'channels'    => ['email'],
                'default_enabled' => true,
                'default_offset_hours' => 24,
                'variables'   => ['client_first_name', 'client_last_name', 'salon_name', 'appointment_date', 'appointment_time', 'staff_name', 'service_names', 'reference', 'salon_phone'],
                'default_templates' => [
                    'email_subject' => 'Reminder: appointment at {{salon_name}} on {{appointment_date}}',
                    'email_body'    => "Hi {{client_first_name}},\n\nThis is a reminder for your appointment on {{appointment_date}} at {{appointment_time}} with {{staff_name}}.\nRef: {{reference}}\n\nSee you soon,\n{{salon_name}}",
                ],
            ],
            'client_appointment_reminder_sms' => [
                'label'       => 'Client — reminder (SMS)',
                'description' => 'Scheduled SMS to the client before their appointment.',
                'timing'      => 'scheduled',
                'channels'    => ['sms'],
                'default_enabled' => true,
                'default_offset_hours' => 24,
                'variables'   => ['client_first_name', 'salon_name', 'appointment_date', 'appointment_time', 'reference'],
                'default_templates' => [
                    'sms_body' => 'Hi {{client_first_name}}, reminder: appt at {{salon_name}} on {{appointment_date}} {{appointment_time}}. Ref {{reference}}',
                ],
            ],
            'tenant_new_client_email' => [
                'label'       => 'Salon — new client registered',
                'description' => 'In-app alert and email to the salon when a new client profile is created.',
                'timing'      => 'instant',
                'channels'    => ['email', 'in_app'],
                'default_enabled' => true,
                'default_offset_hours' => null,
                'variables'   => ['client_first_name', 'client_last_name', 'client_email', 'client_phone', 'salon_name'],
                'default_templates' => [
                    'email_subject' => 'New client: {{client_first_name}} {{client_last_name}}',
                    'email_body'    => "A new client was added to {{salon_name}}:\n\n{{client_first_name}} {{client_last_name}}\nEmail: {{client_email}}\nPhone: {{client_phone}}",
                ],
            ],
        ];
    }

    /**
     * Merge saved JSON + legacy flat keys from settings pluck.
     *
     * @param  array<string, mixed>  $settingsPluck  key => value from salon settings
     * @return array{version:int, rules: array<string, array<string, mixed>>, templates: array<string, array<string, string>>, quiet_hours: array<string, mixed>}
     */
    public function mergedConfigArray(Salon $salon, array $settingsPluck): array
    {
        $definitions = self::definitions();
        $raw = $settingsPluck[self::CONFIG_KEY] ?? null;
        $decoded = null;
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
        } elseif (is_array($raw)) {
            $decoded = $raw;
        }

        $rules = [];
        $templates = [];
        foreach ($definitions as $id => $def) {
            $rules[$id] = [
                'enabled'      => (bool) ($def['default_enabled'] ?? false),
                'offset_hours' => $def['default_offset_hours'],
            ];
            if (! empty($def['default_templates'])) {
                $templates[$id] = $def['default_templates'];
            }
        }

        if (is_array($decoded)) {
            foreach ($decoded['rules'] ?? [] as $id => $row) {
                if (! isset($rules[$id])) {
                    continue;
                }
                if (array_key_exists('enabled', $row)) {
                    $rules[$id]['enabled'] = (bool) $row['enabled'];
                }
                if (array_key_exists('offset_hours', $row) && $row['offset_hours'] !== null) {
                    $rules[$id]['offset_hours'] = (int) $row['offset_hours'];
                }
            }
            foreach ($decoded['templates'] ?? [] as $id => $tpl) {
                if (! isset($templates[$id])) {
                    $templates[$id] = [];
                }
                $templates[$id] = array_merge($templates[$id], array_filter((array) $tpl, fn ($v) => $v !== null && $v !== ''));
            }
            $quiet = $decoded['quiet_hours'] ?? [];
        } else {
            $quiet = [];
            $this->applyLegacySettings($rules, $templates, $settingsPluck);
        }

        $quiet = array_merge([
            'enabled' => false,
            'from'    => '22:00',
            'to'      => '07:00',
            'mode'    => 'skip',
        ], is_array($quiet) ? $quiet : []);

        return [
            'version'      => 2,
            'rules'        => $rules,
            'templates'    => $templates,
            'quiet_hours'  => $quiet,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $rules
     * @param  array<string, array<string, string>>  $templates
     * @param  array<string, mixed>  $settingsPluck
     */
    private function applyLegacySettings(array &$rules, array &$templates, array $settingsPluck): void
    {
        $h = (int) ($settingsPluck['reminder_hours_before'] ?? 24);
        if (isset($rules['client_appointment_reminder_email'])) {
            $rules['client_appointment_reminder_email']['enabled'] = (bool) ($settingsPluck['email_appointment_reminder'] ?? true);
            $rules['client_appointment_reminder_email']['offset_hours'] = $h;
        }
        if (isset($rules['client_appointment_reminder_sms'])) {
            $rules['client_appointment_reminder_sms']['enabled'] = (bool) ($settingsPluck['sms_appointment_reminder'] ?? true);
            $rules['client_appointment_reminder_sms']['offset_hours'] = $h;
        }
        if (isset($rules['client_booking_confirmation_email'])) {
            $rules['client_booking_confirmation_email']['enabled'] = (bool) ($settingsPluck['email_appointment_confirmation'] ?? true);
        }
        if (isset($rules['tenant_new_client_email'])) {
            $rules['tenant_new_client_email']['enabled'] = (bool) ($settingsPluck['email_new_client'] ?? true);
        }
    }

    public function persist(Salon $salon, array $payload): void
    {
        $salon->settings()->updateOrCreate(
            ['key' => self::CONFIG_KEY],
            ['value' => json_encode($payload, JSON_THROW_ON_ERROR), 'type' => 'json']
        );
    }

    public function isRuleEnabled(Salon $salon, string $ruleId, array $settingsPluck): bool
    {
        $cfg = $this->mergedConfigArray($salon, $settingsPluck);

        return (bool) ($cfg['rules'][$ruleId]['enabled'] ?? false);
    }

    public function offsetHours(Salon $salon, string $ruleId, array $settingsPluck): int
    {
        $cfg = $this->mergedConfigArray($salon, $settingsPluck);

        return (int) ($cfg['rules'][$ruleId]['offset_hours'] ?? 24);
    }

    /**
     * @return array{subject?: string, body?: string, sms_body?: string}
     */
    public function templatesForRule(Salon $salon, string $ruleId, array $settingsPluck): array
    {
        $cfg = $this->mergedConfigArray($salon, $settingsPluck);
        $definitions = self::definitions();
        $defaults = $definitions[$ruleId]['default_templates'] ?? [];

        return array_merge($defaults, $cfg['templates'][$ruleId] ?? []);
    }

    public function quietHours(Salon $salon, array $settingsPluck): array
    {
        $cfg = $this->mergedConfigArray($salon, $settingsPluck);

        return $cfg['quiet_hours'];
    }

    /**
     * @param  array<string, string>  $context
     */
    public function render(string $template, array $context): string
    {
        $allowed = array_keys($context);
        $out = $template;
        foreach ($allowed as $key) {
            $val = (string) ($context[$key] ?? '');
            $out = str_replace('{{'.$key.'}}', $val, $out);
        }

        return preg_replace('/\{\{[a-z0-9_]+\}\}/i', '', $out) ?? $out;
    }

    /**
     * @return array<string, string>
     */
    public function buildAppointmentContext(Appointment $appointment): array
    {
        $appointment->loadMissing(['client', 'staff', 'salon', 'services.service']);
        $salon = $appointment->salon;
        $client = $appointment->client;
        $staff = $appointment->staff;
        $names = $appointment->services->map(fn ($as) => $as->service?->name)->filter()->implode(', ');

        $tz = $salon?->timezone ?: config('app.timezone');
        $starts = $appointment->starts_at?->copy()->timezone($tz);

        return [
            'client_first_name' => $client?->first_name ?? '',
            'client_last_name'  => $client?->last_name ?? '',
            'client_email'      => $client?->email ?? '',
            'client_phone'      => $client?->phone ?? '',
            'salon_name'        => $salon?->name ?? '',
            'salon_phone'       => $salon?->phone ?? '',
            'salon_email'       => $salon?->email ?? '',
            'staff_name'        => $staff?->name ?? '',
            'service_names'     => $names ?: 'Your services',
            'reference'         => $appointment->reference ?? '',
            'appointment_date'  => $starts ? $starts->format('l j M Y') : '',
            'appointment_time'  => $starts ? $starts->format('g:i A') : '',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function buildClientContext(Client $client, Salon $salon): array
    {
        return [
            'client_first_name' => $client->first_name ?? '',
            'client_last_name'  => $client->last_name ?? '',
            'client_email'      => $client->email ?? '',
            'client_phone'      => $client->phone ?? '',
            'salon_name'        => $salon->name ?? '',
            'salon_phone'       => $salon->phone ?? '',
            'salon_email'       => $salon->email ?? '',
        ];
    }

    public static function dispatchKey(string $ruleId, int $offsetHours): string
    {
        return $ruleId.':'.$offsetHours;
    }

    public function hasDispatchKey(Appointment $appointment, string $key): bool
    {
        $keys = $appointment->reminder_dispatch_keys;
        if (! is_array($keys)) {
            return false;
        }

        return in_array($key, $keys, true);
    }

    public function markDispatchKey(Appointment $appointment, string $key): void
    {
        $keys = $appointment->reminder_dispatch_keys;
        if (! is_array($keys)) {
            $keys = [];
        }
        if (! in_array($key, $keys, true)) {
            $keys[] = $key;
        }
        $appointment->reminder_dispatch_keys = $keys;
        $appointment->reminder_sent = true;
        if (! $appointment->reminder_sent_at) {
            $appointment->reminder_sent_at = now();
        }
        $appointment->save();
    }

    public function inQuietHours(Salon $salon, array $settingsPluck): bool
    {
        $qh = $this->quietHours($salon, $settingsPluck);
        if (empty($qh['enabled'])) {
            return false;
        }
        $tz = $salon->timezone ?: config('app.timezone');
        $now = now()->timezone($tz)->format('H:i');
        $from = $qh['from'] ?? '22:00';
        $to = $qh['to'] ?? '07:00';
        if ($from <= $to) {
            return $now >= $from && $now <= $to;
        }

        return $now >= $from || $now <= $to;
    }

    /**
     * Skip sending during quiet hours when mode is "skip".
     */
    public function shouldSkipForQuietHours(Salon $salon, array $settingsPluck): bool
    {
        $qh = $this->quietHours($salon, $settingsPluck);
        if (empty($qh['enabled']) || ($qh['mode'] ?? 'skip') !== 'skip') {
            return false;
        }

        return $this->inQuietHours($salon, $settingsPluck);
    }
}

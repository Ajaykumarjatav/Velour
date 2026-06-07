<?php

namespace App\Support;

/**
 * Channel and notification-rule metadata for marketing automation templates.
 */
class MarketingAutomationCatalog
{
    /**
     * @return array<string, array{
     *     channels: list<string>,
     *     notification_rules?: array<string, string>,
     *     default_whatsapp_body?: string
     * }>
     */
    public static function definitions(): array
    {
        return [
            'appointment_reminder' => [
                'channels' => ['email', 'sms'],
                'notification_rules' => [
                    'email' => 'client_appointment_reminder_email',
                    'sms'   => 'client_appointment_reminder_sms',
                ],
            ],
            'booking_confirmation' => [
                'channels' => ['email', 'whatsapp'],
                'notification_rules' => [
                    'email'    => 'client_booking_confirmation_email',
                    'whatsapp' => 'client_booking_confirmation_whatsapp',
                ],
                'default_whatsapp_body' => "Hi {{client_first_name}}, your appointment at {{salon_name}} is confirmed for {{appointment_date}} at {{appointment_time}}.\nServices: {{service_names}}\nRef: {{reference}}",
            ],
            'no_show_followup' => [
                'channels' => ['sms'],
            ],
            'birthday_offer' => [
                'channels' => ['email', 'sms'],
            ],
            're_engagement' => [
                'channels' => ['sms'],
            ],
            'review_request' => [
                'channels' => ['email'],
            ],
        ];
    }

    /**
     * @return array{channels: list<string>, notification_rules?: array<string, string>, default_whatsapp_body?: string}|null
     */
    public static function forKey(string $templateKey): ?array
    {
        return self::definitions()[$templateKey] ?? null;
    }

    /** @return list<string> */
    public static function channelsForKey(string $templateKey): array
    {
        return self::forKey($templateKey)['channels'] ?? ['email'];
    }

    public static function channelLabel(string $channel): string
    {
        return match ($channel) {
            'email'    => 'Email',
            'sms'      => 'SMS',
            'whatsapp' => 'WhatsApp',
            default    => ucfirst($channel),
        };
    }

    /**
     * @param  array{channel_email?: bool, channel_sms?: bool, channel_whatsapp?: bool}  $flags
     */
    public static function channelsLabelFor(string $templateKey, array $flags): string
    {
        $labels = [];
        foreach (self::channelsForKey($templateKey) as $channel) {
            $col = 'channel_'.$channel;
            if (! empty($flags[$col])) {
                $labels[] = self::channelLabel($channel);
            }
        }

        return $labels !== [] ? implode(' + ', $labels) : 'None';
    }
}

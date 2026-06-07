<?php

namespace App\Services;

use App\Models\MarketingAutomationTemplate;
use App\Models\Salon;
use App\Support\MarketingAutomationCatalog;

/**
 * Keeps salon notification_config in sync with marketing automation templates.
 */
class MarketingNotificationBridge
{
    public function __construct(private NotificationConfigService $notificationConfig) {}

    public function sync(MarketingAutomationTemplate $template): void
    {
        $meta = MarketingAutomationCatalog::forKey($template->template_key);
        if (empty($meta['notification_rules'])) {
            return;
        }

        $salon = $template->salon ?? Salon::find($template->salon_id);
        if (! $salon) {
            return;
        }

        $pluck = $salon->settings()->pluck('value', 'key')->all();
        $cfg = $this->notificationConfig->mergedConfigArray($salon, $pluck);

        foreach ($meta['notification_rules'] as $channel => $ruleId) {
            $col = 'channel_'.$channel;
            $enabled = $template->is_active && (bool) ($template->{$col} ?? false);
            if (isset($cfg['rules'][$ruleId])) {
                $cfg['rules'][$ruleId]['enabled'] = $enabled;
            }

            if (! isset($cfg['templates'][$ruleId])) {
                $cfg['templates'][$ruleId] = [];
            }

            if ($channel === 'email') {
                if ($template->email_subject) {
                    $cfg['templates'][$ruleId]['email_subject'] = $template->email_subject;
                }
                if ($template->email_body) {
                    $cfg['templates'][$ruleId]['email_body'] = $template->email_body;
                }
            } elseif ($channel === 'sms' && $template->sms_body) {
                $cfg['templates'][$ruleId]['sms_body'] = $template->sms_body;
            } elseif ($channel === 'whatsapp' && $template->whatsapp_body) {
                $cfg['templates'][$ruleId]['whatsapp_body'] = $template->whatsapp_body;
            }
        }

        $this->notificationConfig->persist($salon, [
            'version'     => $cfg['version'],
            'rules'       => $cfg['rules'],
            'templates'   => $cfg['templates'],
            'quiet_hours' => $cfg['quiet_hours'],
        ]);
    }
}

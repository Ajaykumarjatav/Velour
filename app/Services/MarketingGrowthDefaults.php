<?php

namespace App\Services;

use App\Models\MarketingAutomationTemplate;
use App\Models\MarketingSmsMessage;
use App\Models\MarketingSmsThread;
use App\Models\LoyaltyTier;
use App\Models\Salon;
use App\Models\SalonReferralSetting;
use Illuminate\Support\Facades\Schema;

class MarketingGrowthDefaults
{
    public static function ensureForSalon(Salon $salon): void
    {
        if (! Schema::hasTable('loyalty_tiers')) {
            return;
        }

        if (LoyaltyTier::where('salon_id', $salon->id)->doesntExist()) {
            $defaults = [
                [
                    'name' => 'Glow Silver', 'slug' => 'silver', 'price_monthly' => 2999, 'service_discount_percent' => 10,
                    'sort_order' => 1,
                    'benefits' => ['10% off all services', 'Free facial monthly', 'Priority booking'],
                ],
                [
                    'name' => 'Glow Gold', 'slug' => 'gold', 'price_monthly' => 5999, 'service_discount_percent' => 20,
                    'sort_order' => 2,
                    'benefits' => ['20% off all services', 'Free hair spa monthly', 'VIP slot access', 'Birthday bonus'],
                ],
                [
                    'name' => 'Glow Platinum', 'slug' => 'platinum', 'price_monthly' => 9999, 'service_discount_percent' => 30,
                    'sort_order' => 3,
                    'benefits' => ['30% off everything', 'Unlimited facials', 'Personal stylist', 'Home visits', 'Free products'],
                ],
            ];
            foreach ($defaults as $row) {
                LoyaltyTier::create(array_merge($row, ['salon_id' => $salon->id, 'is_active' => true]));
            }
        }

        SalonReferralSetting::firstOrCreate(
            ['salon_id' => $salon->id],
            [
                'referrer_reward_amount' => 500,
                'referee_reward_amount'  => 300,
                'minimum_spend'          => 1500,
                'credit_expiry_days'     => 90,
            ]
        );

        $templates = [
            [
                'template_key' => 'appointment_reminder', 'name' => 'Appointment Reminder',
                'channels_label' => 'SMS + Email', 'trigger_label' => '24h before appointment', 'is_active' => true,
            ],
            [
                'template_key' => 'booking_confirmation', 'name' => 'Booking Confirmation',
                'channels_label' => 'Email', 'trigger_label' => 'On booking', 'is_active' => true,
            ],
            [
                'template_key' => 'no_show_followup', 'name' => 'No-Show Follow-up',
                'channels_label' => 'SMS', 'trigger_label' => '1h after missed appointment', 'is_active' => true,
            ],
            [
                'template_key' => 'birthday_offer', 'name' => 'Birthday Offer',
                'channels_label' => 'Email + SMS', 'trigger_label' => 'On birthday', 'is_active' => false,
            ],
            [
                'template_key' => 're_engagement', 'name' => 'Re-engagement',
                'channels_label' => 'SMS', 'trigger_label' => '30 days inactive', 'is_active' => true,
            ],
            [
                'template_key' => 'review_request', 'name' => 'Review Request',
                'channels_label' => 'Email', 'trigger_label' => '2h after checkout', 'is_active' => true,
            ],
        ];
        foreach ($templates as $t) {
            MarketingAutomationTemplate::firstOrCreate(
                ['salon_id' => $salon->id, 'template_key' => $t['template_key']],
                array_merge($t, ['salon_id' => $salon->id])
            );
        }

        if (MarketingSmsThread::where('salon_id', $salon->id)->doesntExist()) {
            $thread = MarketingSmsThread::create([
                'salon_id'         => $salon->id,
                'client_id'        => null,
                'display_name'     => 'Priya Sharma',
                'phone'            => '+91 98765 43210',
                'last_preview'     => 'Yes please, that would be great!',
                'last_message_at'  => now()->subMinutes(2),
                'unread_inbound'   => 1,
            ]);
            $now = now();
            MarketingSmsMessage::insert([
                [
                    'thread_id' => $thread->id, 'direction' => 'in', 'body' => 'Hi, can I reschedule my 4pm to 3pm tomorrow?',
                    'created_at' => $now->copy()->subMinutes(8),
                ],
                [
                    'thread_id' => $thread->id, 'direction' => 'out', 'body' => 'Hi Priya! Let me check availability for you.',
                    'created_at' => $now->copy()->subMinutes(7),
                ],
                [
                    'thread_id' => $thread->id, 'direction' => 'out', 'body' => 'Yes, 3PM is available with Anika. Shall I update your booking?',
                    'created_at' => $now->copy()->subMinutes(6),
                ],
                [
                    'thread_id' => $thread->id, 'direction' => 'in', 'body' => 'Yes please, that would be great!',
                    'created_at' => $now->copy()->subMinutes(2),
                ],
            ]);
        }
    }
}

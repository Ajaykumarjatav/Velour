<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Review;
use App\Models\Salon;
use App\Models\SalonNotification;
use App\Models\SalonSetting;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// ════════════════════════════════════════════════════════════════════════════
// MarketingSeeder — 12 campaigns in various states
// ════════════════════════════════════════════════════════════════════════════
class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        $salon = Salon::first();
        $staff = Staff::where('salon_id', $salon->id)->first();

        $campaigns = [
            [
                'name'            => 'Summer Colour Refresh',
                'subject'         => '☀️ Your summer colour is calling, {{first_name}}',
                'type'            => 'email',
                'status'          => 'sent',
                'target'          => 'all',
                'content'         => '<p>Hi {{first_name}}, summer is here and your hair deserves to shine. Book your colour refresh and enjoy 15% off this month.</p>',
                'recipient_count' => 112,
                'sent_count'      => 109,
                'opened_count'    => 64,
                'clicked_count'   => 28,
                'booking_count'   => 18,
                'revenue_generated'=> 3240.00,
                'sent_at'         => now()->subDays(14),
            ],
            [
                'name'            => 'VIP Loyalty Reward',
                'subject'         => '💛 A gift for you, {{first_name}}',
                'type'            => 'email',
                'status'          => 'sent',
                'target'          => 'vip',
                'content'         => '<p>As one of our most valued clients, we\'d love to treat you. Enjoy a complimentary scalp treatment on your next visit.</p>',
                'recipient_count' => 20,
                'sent_count'      => 20,
                'opened_count'    => 18,
                'clicked_count'   => 14,
                'booking_count'   => 11,
                'revenue_generated'=> 1680.00,
                'sent_at'         => now()->subDays(28),
            ],
            [
                'name'            => 'We Miss You — 3 Month Re-engagement',
                'subject'         => 'It\'s been a while, {{first_name}} 💕',
                'type'            => 'email',
                'status'          => 'sent',
                'target'          => 'lapsed',
                'content'         => '<p>We noticed it\'s been a few months, {{first_name}}. We\'d love to welcome you back. Use code WELCOME20 for 20% off your next visit.</p>',
                'recipient_count' => 45,
                'sent_count'      => 44,
                'opened_count'    => 22,
                'clicked_count'   => 9,
                'booking_count'   => 6,
                'revenue_generated'=> 870.00,
                'sent_at'         => now()->subDays(7),
            ],
            [
                'name'            => 'Birthday Treat — August',
                'subject'         => '🎂 Happy Birthday {{first_name}}! Your gift inside',
                'type'            => 'email',
                'status'          => 'sent',
                'target'          => 'birthday',
                'content'         => '<p>Happy Birthday {{first_name}}! Celebrate in style — enjoy a complimentary blowdry when you book any colour this month.</p>',
                'recipient_count' => 8,
                'sent_count'      => 8,
                'opened_count'    => 7,
                'clicked_count'   => 6,
                'booking_count'   => 4,
                'revenue_generated'=> 640.00,
                'sent_at'         => now()->subDays(3),
            ],
            [
                'name'            => 'SMS Flash Offer — Free Olaplex',
                'subject'         => null,
                'type'            => 'sms',
                'status'          => 'sent',
                'target'          => 'all',
                'content'         => 'Hi {{first_name}}, book any colour this week and receive a FREE Olaplex treatment (worth £55). Limited slots — book now: velour.app/book',
                'recipient_count' => 89,
                'sent_count'      => 89,
                'opened_count'    => 89,
                'clicked_count'   => 41,
                'booking_count'   => 23,
                'revenue_generated'=> 4140.00,
                'sent_at'         => now()->subDays(21),
            ],
            [
                'name'            => 'Autumn Glow Promo',
                'subject'         => '🍂 Autumn looks are in, {{first_name}}',
                'type'            => 'email',
                'status'          => 'scheduled',
                'target'          => 'all',
                'content'         => '<p>Rich, warm tones are having a moment this season. Our colour team is ready to transform your look.</p>',
                'recipient_count' => 115,
                'sent_count'      => 0,
                'opened_count'    => 0,
                'clicked_count'   => 0,
                'booking_count'   => 0,
                'revenue_generated'=> 0,
                'scheduled_at'    => now()->addDays(7),
            ],
            [
                'name'            => 'New Client Welcome',
                'subject'         => '🌟 Welcome to Maison Lumière, {{first_name}}',
                'type'            => 'email',
                'status'          => 'draft',
                'target'          => 'new',
                'content'         => '<p>Welcome, {{first_name}}! We\'re so glad you joined our community. Here\'s everything you need to know before your first visit.</p>',
                'recipient_count' => 0,
                'sent_count'      => 0,
                'opened_count'    => 0,
                'clicked_count'   => 0,
                'booking_count'   => 0,
                'revenue_generated'=> 0,
            ],
            [
                'name'            => 'Christmas Gifting — Gift Vouchers',
                'subject'         => '🎁 Give the gift of luxury this Christmas',
                'type'            => 'email',
                'status'          => 'draft',
                'target'          => 'all',
                'content'         => '<p>Looking for the perfect gift? Maison Lumière gift vouchers are available in £25, £50 and £100 denominations.</p>',
                'recipient_count' => 0,
                'sent_count'      => 0,
                'opened_count'    => 0,
                'clicked_count'   => 0,
                'booking_count'   => 0,
                'revenue_generated'=> 0,
            ],
        ];

        foreach ($campaigns as $c) {
            MarketingCampaign::create(array_merge($c, [
                'salon_id'   => $salon->id,
                'created_by' => $staff->id,
            ]));
        }

        $this->command->info('   ✓  ' . count($campaigns) . ' marketing campaigns created.');
    }
}

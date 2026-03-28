<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * HelpArticleSeeder — AUDIT FIX: Support & Help Center
 *
 * Seeds the in-app knowledge base with starter articles for each category.
 */
class HelpArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            // ── Getting Started ──────────────────────────────────────────
            [
                'slug'        => 'getting-started-with-velour',
                'title'       => 'Getting Started with Velour',
                'category'    => 'getting-started',
                'excerpt'     => 'A quick guide to setting up your salon in under 5 minutes.',
                'content'     => "# Getting Started with Velour\n\nWelcome to Velour! This guide walks you through the key steps to set up your salon.\n\n## 1. Complete Your Salon Profile\n\nNavigate to **Settings → Salon Profile** and fill in your name, address, and contact details.\n\n## 2. Set Your Opening Hours\n\nGo to **Settings → Opening Hours** and set when you're open each day.\n\n## 3. Add Your Services\n\nHead to **Services** and create your first service category (e.g. \"Hair\") then add individual services.\n\n## 4. Invite Your Team\n\nGo to **Staff → Invite** to add team members. They'll receive an email invitation.\n\n## 5. Test Your Booking Page\n\nVisit your public booking URL (shown in **Settings → Booking**) to confirm everything looks right.",
                'sort_order'  => 1,
                'is_published'=> true,
                'is_featured' => true,
            ],
            [
                'slug'        => 'adding-your-first-service',
                'title'       => 'Adding Your First Service',
                'category'    => 'getting-started',
                'excerpt'     => 'Create services with pricing, duration, and staff assignments.',
                'content'     => "# Adding Your First Service\n\nServices are what your clients book. Here's how to create one.\n\n## Create a Category\n\nFirst, create a category to group your services (e.g. \"Colour\", \"Cuts\", \"Treatments\").\n\n1. Go to **Services → Categories**\n2. Click **New Category**\n3. Give it a name and optional colour\n\n## Create the Service\n\n1. Go to **Services**\n2. Click **New Service**\n3. Fill in: name, duration, price, and which staff members can perform it\n4. Toggle **Online bookable** to make it visible on your public booking page\n\n## Tips\n\n- Set a **buffer time** (e.g. 10 minutes) to give staff time between appointments\n- Use **deposits** for long services to reduce no-shows",
                'sort_order'  => 2,
                'is_published'=> true,
                'is_featured' => false,
            ],
            // ── Billing ──────────────────────────────────────────────────
            [
                'slug'        => 'understanding-your-subscription',
                'title'       => 'Understanding Your Subscription',
                'category'    => 'billing',
                'excerpt'     => 'Plans, billing cycles, upgrades, and cancellations explained.',
                'content'     => "# Understanding Your Subscription\n\n## Plans\n\nVelour offers four plans: **Starter**, **Growth**, **Pro**, and **Enterprise**. You can compare features on the [Billing → Plans](/billing) page.\n\n## Free Trial\n\nAll new accounts start on a 14-day free trial of the Growth plan. No credit card is required.\n\n## Billing Cycle\n\nSubscriptions renew monthly or annually (annual = 2 months free). Billing happens on the same date each cycle.\n\n## Changing Plans\n\nYou can upgrade or downgrade at any time from **Billing → Change Plan**. Upgrades take effect immediately. Downgrades take effect at the next renewal.\n\n## Cancellation\n\nCancel from **Billing → Cancel Subscription**. You'll retain access until the end of your paid period. No partial refunds.",
                'sort_order'  => 1,
                'is_published'=> true,
                'is_featured' => true,
            ],
            // ── Appointments ──────────────────────────────────────────────
            [
                'slug'        => 'managing-appointments',
                'title'       => 'Managing Appointments',
                'category'    => 'appointments',
                'excerpt'     => 'Create, reschedule, cancel, and track appointment status.',
                'content'     => "# Managing Appointments\n\n## Creating an Appointment\n\nFrom the **Calendar**, click any empty slot and select the client, service, and staff member.\n\n## Status Flow\n\nAppointments move through these statuses:\n- **Confirmed** → client has booked\n- **Checked In** → client has arrived\n- **Completed** → service done, payment taken\n- **No Show** → client didn't arrive\n- **Cancelled** → cancelled by either party\n\n## Reminders\n\nVelour automatically sends reminders 24 hours and 2 hours before each appointment. You can customise this in **Settings → Notifications**.\n\n## Rescheduling\n\nClick the appointment in the calendar and select **Reschedule**. The client will be notified automatically.",
                'sort_order'  => 1,
                'is_published'=> true,
                'is_featured' => false,
            ],
            // ── Troubleshooting ───────────────────────────────────────────
            [
                'slug'        => 'client-cannot-book-online',
                'title'       => 'Clients Cannot Book Online',
                'category'    => 'troubleshooting',
                'excerpt'     => 'Common reasons why online booking might not be working.',
                'content'     => "# Clients Cannot Book Online\n\nIf clients are having trouble booking, check the following:\n\n## 1. Online Booking is Enabled\n\nGo to **Settings → Booking** and confirm **Online booking enabled** is toggled on.\n\n## 2. Services are Set as Bookable\n\nEach service has an **Online bookable** toggle. Confirm the services you want available are enabled.\n\n## 3. Staff are Available\n\nCheck that your staff have **Bookable online** enabled in their profiles, and that they have working hours set.\n\n## 4. Opening Hours are Configured\n\nIf you haven't set opening hours, no slots will appear in the booking widget.\n\n## Still stuck?\n\nEmail [support@velour.app](mailto:support@velour.app) with your booking URL and we'll investigate.",
                'sort_order'  => 1,
                'is_published'=> true,
                'is_featured' => false,
            ],
        ];

        foreach ($articles as $article) {
            DB::table('help_articles')->updateOrInsert(
                ['slug' => $article['slug']],
                array_merge($article, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        $this->command->info('Help articles seeded: ' . count($articles));
    }
}

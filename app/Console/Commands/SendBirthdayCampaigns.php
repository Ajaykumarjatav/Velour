<?php
namespace App\Console\Commands;

use App\Jobs\SendMarketingCampaign;
use App\Models\Client;
use App\Models\Salon;
use Illuminate\Console\Command;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class SendBirthdayCampaigns extends Command
{
    protected $signature   = 'velour:send-birthdays';
    protected $description = 'Send birthday greeting campaigns to clients with today\'s birthday';

    public function handle(): int
    {
        $today = now();

        $clients = Client::with('salon')
            ->where('status', 'active')
            ->where('marketing_consent', true)
            ->whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', $today->day)
            ->get();

        $this->info("Found {$clients->count()} birthday clients today.");

        foreach ($clients as $client) {
            // Dispatch a generic birthday notification (no campaign needed)
            \App\Services\NotificationService::sendBirthdayGreeting($client);
            $this->line("  ✓ Birthday greeting queued for {$client->first_name} {$client->last_name}");
        }

        return Command::SUCCESS;
    }
}

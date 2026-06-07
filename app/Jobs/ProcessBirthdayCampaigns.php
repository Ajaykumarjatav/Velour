<?php
namespace App\Jobs;
use App\Models\Client;
use App\Models\Salon;
use App\Services\MarketingService;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessBirthdayCampaigns implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function handle(): void
    {
        $today = Carbon::today();

        Client::with('salon')
            ->whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', $today->day)
            ->where('marketing_consent', true)
            ->where('status', 'active')
            ->chunk(100, function ($clients) {
                foreach ($clients as $client) {
                    try {
                        // Queue individual birthday email
                        SendMarketingEmail::dispatch($client, 'birthday');
                    } catch (\Exception $e) {
                        Log::warning("Birthday email failed for client {$client->id}: " . $e->getMessage());
                    }
                }
            });
    }
}

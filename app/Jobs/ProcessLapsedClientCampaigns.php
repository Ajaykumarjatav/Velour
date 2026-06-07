<?php
namespace App\Jobs;
use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class ProcessLapsedClientCampaigns implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function handle(): void
    {
        Client::where('last_visit_at', '<', Carbon::now()->subDays(90))
            ->where('marketing_consent', true)
            ->where('status', 'active')
            ->whereNull('win_back_sent_at')
            ->chunk(100, function ($clients) {
                foreach ($clients as $client) {
                    SendMarketingEmail::dispatch($client, 'win_back');
                    $client->update(['win_back_sent_at' => now()]);
                }
            });
    }
}

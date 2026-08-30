<?php
namespace App\Jobs;

use App\Models\Client;
use App\Models\MarketingCampaign;
use App\Services\NotificationService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Support\Facades\Log;

class SendMarketingCampaign implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly MarketingCampaign $campaign,
        public readonly Client            $client,
    ) {}

    public function middleware(): array
    {
        return [new ThrottlesExceptions(5, 10)];
    }

    public function handle(NotificationService $notifications): void
    {
        if ($this->batch()?->cancelled()) return;

        try {
            $type = $this->campaign->type;

            $textBody = $this->campaign->body ?? $this->campaign->content ?? '';
            if ($type === 'sms') {
                $notifications->sendSms($this->client, $textBody);
            } else {
                $notifications->sendEmail($this->client, $this->campaign);
            }

            $this->campaign->increment('sent_count');
        } catch (\Throwable $e) {
            Log::warning('Marketing send failed for client', [
                'campaign_id' => $this->campaign->id,
                'client_id'   => $this->client->id,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}

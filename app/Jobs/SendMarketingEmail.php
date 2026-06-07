<?php
namespace App\Jobs;
use App\Models\Client;
use App\Models\MarketingCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendMarketingEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries    = 3;
    public int $backoff  = 120;
    public int $timeout  = 30;

    public function __construct(
        public readonly Client $client,
        public readonly string $templateType,
        public readonly ?int $campaignId = null,
    ) {}

    public function handle(): void
    {
        if (! $this->client->email || ! $this->client->email_consent) {
            return;
        }

        try {
            // Mail::to($this->client->email)->send(new \App\Mail\MarketingMail($this->client, $this->templateType));

            if ($this->campaignId) {
                MarketingCampaign::find($this->campaignId)?->increment('sent_count');
            }

            Log::info("Marketing email sent to client {$this->client->id} ({$this->templateType})");
        } catch (\Exception $e) {
            Log::error("Marketing email failed for client {$this->client->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendMarketingEmail job permanently failed", [
            'client_id' => $this->client->id,
            'template'  => $this->templateType,
            'error'     => $exception->getMessage(),
        ]);
    }
}

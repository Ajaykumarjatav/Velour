<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class SendSmsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 20;

    public function __construct(
        public readonly string $to,
        public readonly string $message,
        public readonly ?int   $clientId = null,
    ) {}

    public function handle(): void
    {
        if (! config('services.twilio.sid') || ! config('services.twilio.token')) {
            Log::warning("Twilio not configured — SMS skipped for {$this->to}");
            return;
        }

        try {
            $twilio = new TwilioClient(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $twilio->messages->create($this->to, [
                'from' => config('services.twilio.from'),
                'body' => $this->message,
            ]);

            Log::info("SMS sent to {$this->to}");
        } catch (\Exception $e) {
            Log::error("SMS failed to {$this->to}: " . $e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendSmsNotification permanently failed", [
            'to'    => $this->to,
            'error' => $exception->getMessage(),
        ]);
    }
}

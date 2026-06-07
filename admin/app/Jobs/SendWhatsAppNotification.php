<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 60;
    public int $timeout = 20;

    public function __construct(
        public readonly string $to,
        public readonly string $message,
        public readonly ?int $clientId = null,
    ) {}

    public function handle(): void
    {
        $from = config('services.twilio.whatsapp_from');
        if (! config('services.twilio.sid') || ! config('services.twilio.token') || ! $from) {
            Log::warning("Twilio WhatsApp not configured — message skipped for {$this->to}");

            return;
        }

        try {
            $twilio = new TwilioClient(
                config('services.twilio.sid'),
                config('services.twilio.token')
            );

            $twilio->messages->create($this->normalizeWhatsAppAddress($this->to), [
                'from' => $this->normalizeWhatsAppAddress($from),
                'body' => $this->message,
            ]);

            Log::info("WhatsApp sent to {$this->to}");
        } catch (\Exception $e) {
            Log::error("WhatsApp failed to {$this->to}: ".$e->getMessage());
            throw $e;
        }
    }

    private function normalizeWhatsAppAddress(string $value): string
    {
        $value = trim($value);
        if (str_starts_with($value, 'whatsapp:')) {
            return $value;
        }

        return 'whatsapp:'.$value;
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendWhatsAppNotification permanently failed', [
            'to'    => $this->to,
            'error' => $exception->getMessage(),
        ]);
    }
}

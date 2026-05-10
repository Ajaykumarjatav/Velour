<?php

namespace App\Mail;

use App\Models\PosTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PosTransactionInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PosTransaction $transaction,
    ) {
        $this->transaction->loadMissing(['salon', 'client', 'items']);
    }

    public function envelope(): Envelope
    {
        $salonName = $this->transaction->salon?->name ?? config('app.name');

        return new Envelope(
            subject: 'Your receipt from '.$salonName.' — '.$this->transaction->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pos.invoice',
        );
    }
}

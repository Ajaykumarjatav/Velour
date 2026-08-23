<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Notifications\Support\SupportTicketMailNotification;
use App\Support\SupportTicketUrls;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SupportTicketNotifier
{
    public function created(SupportTicket $ticket): void
    {
        $ticket->loadMissing(['user', 'salon']);
        $excerpt = $this->excerpt((string) $ticket->body);
        $store = $ticket->salon?->name ?? 'a store';

        $this->mailTenant(
            $ticket,
            'created',
            "We received your support ticket {$ticket->ticket_number}. Our team will review it shortly.",
            $excerpt,
        );
        $this->mailStaff(
            $ticket,
            'created',
            "New support ticket {$ticket->ticket_number} from {$store}"
                .($ticket->user ? ' ('.$ticket->user->name.')' : '').'.',
            $excerpt,
        );
        $this->inApp($ticket, 'Support ticket submitted', "{$ticket->ticket_number}: {$ticket->subject}");
    }

    public function publicReply(SupportTicket $ticket, SupportTicketReply $reply): void
    {
        $ticket->loadMissing(['user', 'salon']);
        $excerpt = $this->excerpt((string) $reply->body);
        $fromAdmin = (bool) $reply->is_admin_reply;
        $store = $ticket->salon?->name ?? 'a store';

        if ($fromAdmin) {
            $this->mailTenant(
                $ticket,
                'replied',
                "EasyGrox support replied to ticket {$ticket->ticket_number}.",
                $excerpt,
            );
        } else {
            $this->mailStaff(
                $ticket,
                'replied',
                "Tenant reply on {$ticket->ticket_number} ({$store}).",
                $excerpt,
            );
        }

        $this->inApp(
            $ticket,
            $fromAdmin ? 'Support replied' : 'You replied to support',
            "{$ticket->ticket_number}: {$ticket->subject}",
        );
    }

    public function statusChanged(SupportTicket $ticket, string $previousStatus): void
    {
        if ($previousStatus === $ticket->status) {
            return;
        }

        $ticket->loadMissing(['user', 'salon']);
        $label = ucwords(str_replace('_', ' ', $ticket->status));
        $summary = "Ticket {$ticket->ticket_number} is now {$label}.";

        $this->mailTenant($ticket, 'status', $summary, null);
        $this->mailStaff($ticket, 'status', $summary, null);
        $this->inApp($ticket, 'Support ticket updated', $summary);
    }

    private function mailTenant(SupportTicket $ticket, string $event, string $summary, ?string $excerpt): void
    {
        $user = $ticket->user;
        if (! $user || ! $user->email) {
            return;
        }

        try {
            $user->notify(new SupportTicketMailNotification($ticket, $event, 'tenant', $summary, $excerpt));
        } catch (\Throwable $e) {
            Log::warning('Support ticket tenant mail failed', ['ticket' => $ticket->id, 'error' => $e->getMessage()]);
        }
    }

    /** One email per unique address; never repeats the tenant inbox. */
    private function mailStaff(SupportTicket $ticket, string $event, string $summary, ?string $excerpt): void
    {
        $skip = [];
        $tenantEmail = strtolower(trim((string) ($ticket->user?->email ?? '')));
        if ($tenantEmail !== '') {
            $skip[$tenantEmail] = true;
        }

        $admins = User::query()
            ->where('system_role', 'super_admin')
            ->whereNotNull('email')
            ->get();

        foreach ($admins as $admin) {
            $email = strtolower(trim((string) $admin->email));
            if ($email === '' || isset($skip[$email])) {
                continue;
            }
            $skip[$email] = true;
            try {
                $admin->notify(new SupportTicketMailNotification($ticket, $event, 'admin', $summary, $excerpt));
            } catch (\Throwable $e) {
                Log::warning('Support ticket admin mail failed', [
                    'ticket' => $ticket->id,
                    'admin' => $admin->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        foreach ($this->opsEmails() as $ops) {
            if (isset($skip[$ops])) {
                continue;
            }
            $skip[$ops] = true;
            try {
                Notification::route('mail', $ops)
                    ->notify(new SupportTicketMailNotification($ticket, $event, 'admin', $summary, $excerpt));
            } catch (\Throwable $e) {
                Log::warning('Support ticket ops mail failed', ['ticket' => $ticket->id, 'error' => $e->getMessage()]);
            }
        }
    }

    /** @return list<string> */
    private function opsEmails(): array
    {
        $raw = trim((string) config('mail.support_notify', config('mail.ops_notify')));
        $cc = trim((string) config('mail.ops_notify_cc', ''));
        $parts = preg_split('/[\s,;]+/', $raw.','.$cc) ?: [];
        $out = [];
        foreach ($parts as $email) {
            $email = strtolower(trim($email));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $out[] = $email;
            }
        }

        return array_values(array_unique($out));
    }

    private function inApp(SupportTicket $ticket, string $title, string $body): void
    {
        if (! $ticket->salon_id) {
            return;
        }

        try {
            \App\Models\SalonNotification::create([
                'salon_id' => $ticket->salon_id,
                'staff_id' => null,
                'type' => 'system',
                'title' => $title,
                'body' => $body,
                'data' => ['ticket_id' => $ticket->id, 'action_label' => 'View ticket'],
                'action_url' => SupportTicketUrls::tenantShow($ticket),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Support ticket in-app notification failed', ['ticket' => $ticket->id, 'error' => $e->getMessage()]);
        }
    }

    private function excerpt(string $body): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($body)) ?? '');

        return \Illuminate\Support\Str::limit($plain, 400);
    }
}

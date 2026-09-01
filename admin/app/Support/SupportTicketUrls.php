<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\SupportTicket;

final class SupportTicketUrls
{
    public static function tenantShow(SupportTicket $ticket): string
    {
        $ticket->loadMissing('salon');
        $salon = $ticket->salon;
        if (! $salon) {
            return AppUrl::login();
        }

        return route('support-tickets.show', [
            'store' => SalonUrl::key($salon),
            'ticket' => $ticket,
        ]);
    }

    public static function tenantIndex(SupportTicket $ticket): string
    {
        $ticket->loadMissing('salon');
        $salon = $ticket->salon;
        if (! $salon) {
            return AppUrl::login();
        }

        return route('support-tickets.index', [
            'store' => SalonUrl::key($salon),
        ]);
    }

    public static function adminShow(SupportTicket $ticket): string
    {
        return route('admin.support.show', $ticket);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;

class BookingController extends Controller
{
    public function show(string $slug)
    {
        $salon = Salon::where('slug', $slug)->firstOrFail();

        $hasBookableServices = Service::query()
            ->where('salon_id', $salon->id)
            ->where('status', 'active')
            ->where('online_bookable', true)
            ->eligibleForPublicBooking($salon)
            ->exists();
        $hasHours = ! empty($salon->opening_hours);

        if (! $salon->online_booking_enabled || ! $hasBookableServices || ! $hasHours) {
            $reasons = [];
            if (! $salon->online_booking_enabled) $reasons[] = 'Online booking is currently turned off.';
            if (! $hasBookableServices) $reasons[] = 'No online-bookable services are available yet.';
            if (! $hasHours) $reasons[] = 'Opening hours are not configured yet.';

            return response()->view('booking.unavailable', [
                'salon' => $salon,
                'reasons' => $reasons,
            ], 200);
        }

        $publicServiceCount = Service::query()
            ->where('salon_id', $salon->id)
            ->where('status', 'active')
            ->eligibleForPublicBooking($salon)
            ->count();
        $bookableStaffCount = Staff::query()
            ->where('salon_id', $salon->id)
            ->where('is_active', true)
            ->where('bookable_online', true)
            ->count();
        $avgRating = (float) Review::query()
            ->where('salon_id', $salon->id)
            ->where('is_public', true)
            ->avg('rating');
        $reviewCount = Review::query()
            ->where('salon_id', $salon->id)
            ->where('is_public', true)
            ->count();

        return view('booking.show', compact(
            'salon',
            'publicServiceCount',
            'bookableStaffCount',
            'avgRating',
            'reviewCount'
        ));
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function show(string $slug)
    {
        $salon = Salon::where('slug', $slug)->firstOrFail();

        abort_unless($salon->online_booking_enabled, 404, 'Online booking is not available for this salon.');

        return view('booking.show', compact('salon'));
    }
}

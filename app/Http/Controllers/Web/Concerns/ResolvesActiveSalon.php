<?php

namespace App\Http\Controllers\Web\Concerns;

use App\Models\Salon;
use Illuminate\Support\Facades\Auth;

trait ResolvesActiveSalon
{
    protected function activeSalon(): Salon
    {
        $user = Auth::user();
        $activeSalonId = (int) session('active_salon_id', 0);
        $salon = $activeSalonId > 0
            ? $user->salons()->where('id', $activeSalonId)->first()
            : null;

        return $salon ?: $user->salons()->firstOrFail();
    }
}

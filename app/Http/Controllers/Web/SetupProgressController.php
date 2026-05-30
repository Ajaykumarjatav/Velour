<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Salon;
use App\Support\SalonSetupProgress;

class SetupProgressController extends Controller
{
    use ResolvesActiveSalon;

    public function index()
    {
        $salon = $this->salon();
        $progress = SalonSetupProgress::forSalon($salon);

        return view('setup-progress.index', [
            'salon'     => $salon,
            'items'     => $progress['items'],
            'completed' => $progress['completed'],
            'total'     => $progress['total'],
            'percent'   => $progress['percent'],
        ]);
    }

    private function salon(): Salon
    {
        return $this->activeSalon();
    }
}


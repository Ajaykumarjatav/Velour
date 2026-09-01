<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\PublicSalonAccess;
use App\Services\SalonWebsitePayloadService;
use Illuminate\Http\JsonResponse;

class SalonWebsiteController extends Controller
{
    public function show(string $salonSlug, SalonWebsitePayloadService $payload): JsonResponse
    {
        $salon = PublicSalonAccess::findBySlugOrFail($salonSlug);

        return response()->json($payload->build($salon));
    }
}

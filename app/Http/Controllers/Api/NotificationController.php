<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Review;
use App\Models\Staff;
use App\Models\SalonNotification;
use App\Models\SalonSetting;
use App\Models\LinkVisit;
use App\Models\PosTransaction;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');
        $notifications = \App\Models\SalonNotification::where('salon_id', $salonId)
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 30);
        return response()->json($notifications);
    }

    public function markRead(Request $request, int $id): JsonResponse
    {
        $n = \App\Models\SalonNotification::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $n->markRead();
        return response()->json(['message' => 'Marked as read.']);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        \App\Models\SalonNotification::where('salon_id', $request->attributes->get('salon_id'))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        \App\Models\SalonNotification::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id)->delete();
        return response()->json(['message' => 'Notification deleted.']);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = \App\Models\SalonNotification::where('salon_id', $request->attributes->get('salon_id'))
            ->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }
}

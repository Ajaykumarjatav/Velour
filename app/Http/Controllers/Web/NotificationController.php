<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SalonNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    /**
     * Full notifications page — paginated list of all salon notifications.
     */
    public function index(Request $request)
    {
        $salon = $this->salon();

        $filter = $request->get('filter'); // 'unread' | '' (all)

        $query = SalonNotification::where('salon_id', $salon->id)->latest();

        if ($filter === 'unread') {
            $query->where('is_read', false);
        }

        $notifications = $query->paginate(30)->withQueryString();
        $unreadCount   = SalonNotification::where('salon_id', $salon->id)
                            ->where('is_read', false)->count();

        return view('notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }

    /**
     * Mark a single notification as read (AJAX or redirect).
     */
    public function markRead(Request $request, SalonNotification $notification)
    {
        abort_unless($notification->salon_id === $this->salon()->id, 403);

        $notification->markRead();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        // Follow action_url if present, else back
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back();
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead()
    {
        $salon = $this->salon();

        SalonNotification::where('salon_id', $salon->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Return latest 8 unread + recent notifications as JSON (for header dropdown).
     */
    public function dropdown()
    {
        $salon = $this->salon();

        $items = SalonNotification::where('salon_id', $salon->id)
            ->latest()
            ->limit(8)
            ->get(['id', 'type', 'title', 'body', 'is_read', 'action_url', 'created_at']);

        $unreadCount = SalonNotification::where('salon_id', $salon->id)
            ->where('is_read', false)->count();

        return response()->json([
            'notifications' => $items,
            'unread_count'  => $unreadCount,
        ]);
    }
}

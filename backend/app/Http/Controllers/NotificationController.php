<?php

namespace App\Http\Controllers;

use App\Models\NotificationAcademie;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->input('limit', 15);
        $limit = max(1, min($limit, 50));

        $notifications = NotificationAcademie::where('user_id', auth()->id())
            ->with('expediteur')
            ->latest()
            ->take($limit)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $notifications->where('est_lu', false)->count(),
        ]);
    }

    public function markAsRead(NotificationAcademie $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->update(['est_lu' => true]);

        return response()->json([
            'message' => 'Notification marquee comme lue.'
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        NotificationAcademie::where('user_id', auth()->id())
            ->where('est_lu', false)
            ->update(['est_lu' => true]);

        return response()->json([
            'message' => 'Toutes les notifications ont ete marquees comme lues.'
        ]);
    }
}

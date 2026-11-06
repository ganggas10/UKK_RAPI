<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomNotification;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    /**
     * GET /api/notifications
     * Daftar notifikasi pemindahan ruang milik Guru (yang belum dibaca)
     */
    public function index(Request $request)
    {
        if (! in_array($request->user()->role, ['guru', 'organisasi'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $notifications = RoomNotification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $notifications->map(fn ($n) => $this->formatNotification($n)),
            'unread_count' => RoomNotification::where('user_id', $request->user()->id)
                ->where('is_read', false)->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * POST /api/notifications/{id}/read
     * Tandai notifikasi sebagai sudah dibaca
     */
    public function markRead(Request $request, RoomNotification $notification)
    {
        if (! in_array($request->user()->role, ['guru', 'organisasi'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak memiliki izin.'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notifikasi ditandai sudah dibaca.']);
    }

    /**
     * POST /api/notifications/read-all
     * Tandai SEMUA notifikasi sebagai sudah dibaca
     */
    public function markAllRead(Request $request)
    {
        if (! in_array($request->user()->role, ['guru', 'organisasi'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        RoomNotification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca.']);
    }

    // ============================================================
    // Helper: format notification data for API response
    // ============================================================
    private function formatNotification(RoomNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'message' => $notification->message,
            'is_read' => (bool) $notification->is_read,
            'event_id' => $notification->event_id,
            'created_at' => $notification->created_at?->toISOString(),
        ];
    }
}

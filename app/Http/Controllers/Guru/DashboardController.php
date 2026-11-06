<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\RoomNotification;

class DashboardController extends Controller
{
    public function index()
    {
        $myEvents = Event::with('room')
            ->where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();

        // Notifikasi belum dibaca (pemindahan ruang oleh Waka)
        $notifications = RoomNotification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->latest()
            ->get();

        return view('guru.dashboard', compact('myEvents', 'notifications'));
    }

    public function markRead(RoomNotification $notification)
    {
        abort_if($notification->user_id !== auth()->id(), 403);
        $notification->update(['is_read' => true]);

        return back();
    }
}

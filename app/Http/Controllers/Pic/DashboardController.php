<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Room;

class DashboardController extends Controller
{
    public function index()
    {
        $pic = auth()->user();

        // Ruangan yang ditangani PIC ini (baik sebagai PIC utama maupun ko-PIC di pic_assignments)
        $myRooms = Room::where(function ($query) use ($pic) {
            $query->where('pic_id', $pic->id)
                ->orWhereHas('activePicAssignments', fn ($q) => $q->where('pic_id', $pic->id));
        })
            ->withCount('complaints')
            ->withCount(['complaints as open_complaints_count' => fn ($query) => $query->where('status_repair', '!=', 'Sudah Diperbaiki')])
            ->orderBy('id')
            ->get();
        $myRoomIds = $myRooms->pluck('id');

        $stats = [
            'total_rooms' => $myRooms->count(),
            'belum_diperbaiki' => Complaint::whereIn('room_id', $myRoomIds)->where('status_repair', 'Belum Diperbaiki')->count(),
            'menunggu_sparepart' => Complaint::whereIn('room_id', $myRoomIds)->where('status_repair', 'Menunggu Spare Part')->count(),
            'sudah_diperbaiki' => Complaint::whereIn('room_id', $myRoomIds)->where('status_repair', 'Sudah Diperbaiki')->count(),
        ];

        $recent_complaints = Complaint::with([
            'room:id,name,location,pic_id',
            'reporter:id,name,role',
        ])
            ->whereIn('room_id', $myRoomIds)
            ->latest()
            ->take(5)
            ->get();

        return view('pic.dashboard', compact('stats', 'myRooms', 'recent_complaints'));
    }
}

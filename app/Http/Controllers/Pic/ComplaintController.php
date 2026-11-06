<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Room;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    /**
     * Tampilkan semua pengaduan di ruangan yang menjadi tanggung jawab PIC ini.
     */
    public function index()
    {
        $picId = auth()->id();

        // Ambil semua room_id yang menjadi tanggung jawab PIC ini (termasuk multi-PIC)
        $myRoomIds = Room::where('pic_id', $picId)
            ->orWhereHas('activePicAssignments', fn ($q) => $q->where('pic_id', $picId))
            ->pluck('id');

        $complaints = Complaint::with(['room', 'reporter'])
            ->whereIn('room_id', $myRoomIds)
            ->latest()
            ->paginate(15);

        return view('pic.complaints.index', compact('complaints'));
    }

    /**
     * LOGIKA BISNIS #2 & #3:
     * PIC hanya bisa update status ruangan yang dia tanggung jawabi.
     * Status wajib salah satu dari 3: Sudah Diperbaiki, Menunggu Spare Part, Belum Diperbaiki.
     */
    public function updateStatus(Request $request, Complaint $complaint)
    {
        // ============================================================
        // LOGIKA BISNIS #2: CEK DELEGASI PIC
        // PIC hanya bisa ubah complaint yang room-nya dia pegang
        // ============================================================
        $picId = auth()->id();
        $isMyRoom = Room::where('id', $complaint->room_id)
            ->where(function ($query) use ($picId) {
                $query->where('pic_id', $picId)
                    ->orWhereHas('activePicAssignments', fn ($q) => $q->where('pic_id', $picId));
            })
            ->exists();

        if (! $isMyRoom) {
            abort(403, 'Anda tidak memiliki izin untuk mengelola ruangan ini.');
        }

        // ============================================================
        // LOGIKA BISNIS #3: WORKFLOW STATE (3 STATUS MUTLAK)
        // ============================================================
        $validated = $request->validate([
            'status_repair' => 'required|in:Belum Diperbaiki,Menunggu Spare Part,Sudah Diperbaiki',
            'pic_notes' => 'nullable|string|max:500',
        ]);

        $complaint->update($validated);

        return back()->with('success', "Status pengaduan berhasil diperbarui menjadi: {$validated['status_repair']}");
    }
}

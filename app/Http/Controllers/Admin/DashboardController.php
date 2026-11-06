<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Event;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_rooms' => Room::count(),
            'total_users' => User::count(),
            'pending_events' => Event::where('status', 'pending')->count(),
            'open_complaints' => Complaint::where('status_repair', 'Belum Diperbaiki')->count(),
        ];

        $recent_events = Event::with(['room', 'user'])->latest()->take(5)->get();
        $open_complaints = Complaint::with([
            'room:id,name,location,pic_id',
            'room.pic:id,name,phone',
            'reporter:id,name,role',
        ])
            ->where('status_repair', 'Belum Diperbaiki')
            ->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recent_events', 'open_complaints'));
    }

    /**
     * Menampilkan seluruh pengaduan untuk monitoring Admin/Waka.
     * Halaman ini read-only; tindak lanjut dilakukan oleh PIC.
     */
    public function complaints(Request $request): View
    {
        $complaints = Complaint::with([
            'room:id,name,location,pic_id',
            'room.pic:id,name,phone',
            'reporter:id,name,role',
        ])->latest()->paginate(5)->withQueryString();

        return view('admin.complaints.index', compact('complaints'));
    }

    public function createComplaint(): View
    {
        $rooms = Room::orderBy('name')->get(['id', 'name', 'location']);

        return view('admin.complaints.create', compact('rooms'));
    }

    public function storeComplaint(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'photo.required' => 'Foto bukti kerusakan wajib diunggah.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 5MB.',
            'description.min' => 'Deskripsi minimal 10 karakter.',
        ]);

        $validated['reporter_id'] = $request->user()->id;
        $validated['photo_path'] = $request->file('photo')->store('complaints', 'public');
        $validated['status_repair'] = 'Belum Diperbaiki';

        Complaint::create($validated);

        return redirect()->route('admin.complaints.index')
            ->with('success', 'Laporan kerusakan berhasil dikirim ke PIC ruangan.');
    }

    public function sendWhatsApp(Complaint $complaint)
    {
        $complaint->load('room.pic');
        $pic = $complaint->room->pic;

        if (! $pic || ! $pic->phone) {
            return back()->with('error', 'Nomor WhatsApp PIC tidak ditemukan!');
        }

        $message = urlencode(
            "⚠️ *PERINGATAN KERUSAKAN FASILITAS*\n\n".
            "Ruangan: *{$complaint->room->name}*\n".
            "Kerusakan: {$complaint->description}\n".
            "Status: {$complaint->status_repair}\n\n".
            'Mohon segera ditangani. Terima kasih.'
        );

        $waUrl = "https://wa.me/{$pic->phone}?text={$message}";

        return redirect()->away($waUrl);
    }
}

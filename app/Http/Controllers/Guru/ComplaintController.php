<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Room;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        $complaints = Complaint::with(['room'])
            ->where('reporter_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('guru.complaints.index', compact('complaints'));
    }

    public function create()
    {
        $rooms = Room::all();

        return view('guru.complaints.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'description' => 'required|string|min:10|max:1000',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120', // wajib, maks 5MB
        ], [
            'photo.image' => 'File harus berupa gambar.',
            'photo.required' => 'Foto bukti kerusakan wajib diunggah.',
            'photo.max' => 'Ukuran foto maksimal 5MB.',
        ]);

        $photoPath = null;

        // Handle upload foto
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            // Simpan di storage/app/public/complaints/
            $photoPath = $file->store('complaints', 'public');
        }

        Complaint::create([
            'room_id' => $validated['room_id'],
            'reporter_id' => auth()->id(),
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'status_repair' => 'Belum Diperbaiki',
        ]);

        return redirect()->route('guru.complaints.index')
            ->with('success', 'Laporan kerusakan berhasil dikirim!');
    }

    public function show(Complaint $complaint)
    {
        abort_if($complaint->reporter_id !== auth()->id(), 403);
        $complaint->load('room');

        return view('guru.complaints.show', compact('complaint'));
    }
}

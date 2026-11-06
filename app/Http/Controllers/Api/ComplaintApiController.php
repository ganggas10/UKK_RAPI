<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintApiController extends Controller
{
    /**
     * GET /api/complaints
     * Daftar laporan kerusakan milik user yang sedang login (Guru)
     */
    public function index(Request $request)
    {
        if (! in_array($request->user()->role, ['guru', 'organisasi'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $complaints = Complaint::with(['room:id,name,location'])
            ->where('reporter_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => $complaints->map(fn ($c) => $this->formatComplaint($c)),
            'pagination' => [
                'current_page' => $complaints->currentPage(),
                'last_page' => $complaints->lastPage(),
                'per_page' => $complaints->perPage(),
                'total' => $complaints->total(),
            ],
        ]);
    }

    /**
     * GET /api/complaints/{id}
     * Detail laporan kerusakan (hanya pemilik laporan)
     */
    public function show(Request $request, Complaint $complaint)
    {
        if ($complaint->reporter_id !== $request->user()->id) {
            return response()->json(['message' => 'Tidak memiliki izin akses laporan ini.'], 403);
        }

        $complaint->load(['room', 'reporter:id,name']);

        return response()->json(['data' => $this->formatComplaint($complaint, true)]);
    }

    /**
     * POST /api/complaints
     * Kirim laporan kerusakan baru + upload foto (Guru saja)
     *
     * Form data:
     * - room_id (required)
     * - description (required, min:10)
     * - photo (required, image file, max 5MB)
     */
    public function store(Request $request)
    {
        if (! in_array($request->user()->role, ['guru', 'organisasi'])) {
            return response()->json(['message' => 'Hanya Guru/Organisasi yang bisa melaporkan kerusakan.'], 403);
        }

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'description' => 'required|string|min:10|max:1000',
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'photo.image' => 'File harus berupa gambar.',
            'photo.required' => 'Foto bukti kerusakan wajib diunggah.',
            'photo.max' => 'Ukuran foto maksimal 5MB.',
            'description.min' => 'Deskripsi minimal 10 karakter.',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $photoPath = $file->store('complaints', 'public');
        }

        $complaint = Complaint::create([
            'room_id' => $validated['room_id'],
            'reporter_id' => $request->user()->id,
            'description' => $validated['description'],
            'photo_path' => $photoPath,
            'status_repair' => 'Belum Diperbaiki',
        ]);

        $complaint->load('room:id,name');

        return response()->json([
            'message' => 'Laporan kerusakan berhasil dikirim!',
            'data' => $this->formatComplaint($complaint, true),
        ], 201);
    }

    /**
     * GET /api/rooms
     * Daftar semua ruangan (untuk dropdown pilih ruangan)
     */
    public function rooms()
    {
        $rooms = Room::select('id', 'name', 'location', 'capacity')->get();

        return response()->json(['data' => $rooms]);
    }

    // ============================================================
    // Helper: format complaint data for API response
    // ============================================================
    private function formatComplaint(Complaint $complaint, bool $includeDetails = false): array
    {
        $data = [
            'id' => $complaint->id,
            'room' => $complaint->room ? [
                'id' => $complaint->room->id,
                'name' => $complaint->room->name,
                'location' => $complaint->room->location ?? null,
            ] : null,
            'description' => $complaint->description,
            'status_repair' => $complaint->status_repair,
            'created_at' => $complaint->created_at?->toISOString(),
        ];

        if ($complaint->photo_path) {
            $data['photo_url'] = Storage::url($complaint->photo_path);
        } else {
            $data['photo_url'] = null;
        }

        if ($includeDetails) {
            $data['pic_notes'] = $complaint->pic_notes;
            $data['reporter'] = $complaint->reporter ? [
                'id' => $complaint->reporter->id,
                'name' => $complaint->reporter->name,
            ] : null;
            $data['updated_at'] = $complaint->updated_at?->toISOString();
        }

        return $data;
    }
}

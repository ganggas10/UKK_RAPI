<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Room;
use App\Models\SchoolSession;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,approved,rejected,moved'],
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ], [
            'date_to.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $query = Event::with(['room', 'movedToRoom'])
            ->where('user_id', auth()->id());

        // 1. Filter Pencarian Judul
        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where('title', 'like', "%{$search}%");
        }

        // 2. Filter Status Peminjaman
        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        // 3. Filter Ruangan
        if (! empty($validated['room_id'])) {
            $query->where('room_id', $validated['room_id']);
        }

        // 4. Filter Rentang Tanggal
        if (! empty($validated['date_from']) || ! empty($validated['date_to'])) {
            $query->where(function ($dateQuery) use ($validated): void {
                if (! empty($validated['date_to'])) {
                    $dateQuery->whereRaw('date(COALESCE(start_date, date)) <= ?', [$validated['date_to']]);
                }

                if (! empty($validated['date_from'])) {
                    $dateQuery->whereRaw('date(COALESCE(end_date, date)) >= ?', [$validated['date_from']]);
                }
            });
        }

        $events = $query->latest('created_at')->paginate(10)->withQueryString();
        $rooms = Room::orderBy('name')->get();
        $filters = $validated;

        return view('guru.events.index', compact('events', 'rooms', 'filters'));
    }

    public function create()
    {
        $rooms = Room::all();
        $sessions = SchoolSession::active()->orderBy('sort_order')->orderBy('start_time')->get();

        return view('guru.events.create', compact('rooms', 'sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'room_id' => 'required|exists:rooms,id',
            'date' => 'nullable|date|after:today',
            'start_date' => 'required_without:date|date|after:today',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_session' => ['required', 'string', 'exists:school_sessions,name'],
            'end_session' => ['required', 'string', 'exists:school_sessions,name'],
        ], [
            'date.after' => 'Pengajuan peminjaman ruangan wajib dilakukan minimal 1 hari sebelum tanggal penggunaan. Silakan pilih tanggal penggunaan yang lain.',
            'start_date.after' => 'Pengajuan peminjaman ruangan wajib dilakukan minimal 1 hari sebelum tanggal penggunaan. Silakan pilih tanggal penggunaan yang lain.',
            'end_date.after_or_equal' => 'Tanggal selesai peminjaman tidak boleh lebih awal daripada tanggal mulai.',
            'start_session.exists' => 'Sesi mulai yang dipilih tidak valid atau sudah tidak aktif.',
            'end_session.exists' => 'Sesi selesai yang dipilih tidak valid atau sudah tidak aktif.',
        ]);

        $validated['start_date'] = $validated['start_date'] ?? $validated['date'];
        $validated['end_date'] = ! empty($validated['end_date']) ? $validated['end_date'] : $validated['start_date'];
        $validated['date'] = $validated['start_date'];

        $startSessionModel = SchoolSession::where('name', $validated['start_session'])->first();
        $endSessionModel = SchoolSession::where('name', $validated['end_session'])->first();

        if (! $startSessionModel || ! $startSessionModel->is_active || ! $endSessionModel || ! $endSessionModel->is_active) {
            throw ValidationException::withMessages([
                'start_session' => 'Sesi yang dipilih sudah dinonaktifkan.',
            ]);
        }

        if ($this->sessionOrder($endSessionModel) < $this->sessionOrder($startSessionModel)) {
            throw ValidationException::withMessages([
                'end_session' => 'Sesi selesai harus sama atau setelah sesi mulai.',
            ]);
        }

        $validated['start_time'] = substr($startSessionModel->start_time, 0, 5);
        $validated['end_time'] = substr($endSessionModel->end_time, 0, 5);
        $validated['session'] = $validated['start_session'].' - '.$validated['end_session'];
        unset($validated['start_session'], $validated['end_session']);

        // ====================================================
        // LOGIKA BISNIS #1: CONFLICT PREVENTION (WAJIB)
        // Cek apakah ada event lain di ruang & tanggal yang sama
        // dengan waktu yang bertabrakan
        // ====================================================
        $conflict = Event::overlapping(
            $validated['room_id'],
            $validated['start_date'],
            $validated['end_date'],
            $validated['start_time'],
            $validated['end_time'],
        )->first();

        if ($conflict) {
            return back()
                ->withInput()
                ->with('conflict_error',
                    "⛔ Ruangan sudah dipesan pada: {$conflict->title} ".
                    "({$conflict->bookingStartDate()->format('d/m/Y')} - {$conflict->bookingEndDate()->format('d/m/Y')}, {$conflict->start_time} - {$conflict->end_time}). ".
                    'Pilih waktu atau ruangan lain.'
                );
        }

        // Simpan event
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        Event::create($validated);

        return redirect()->route('guru.events.index')
            ->with('success', 'Pengajuan peminjaman berhasil dikirim! Menunggu persetujuan.');
    }

    public function show(Event $event)
    {
        // Pastikan yang lihat adalah pemilik event
        abort_if($event->user_id !== auth()->id(), 403);
        $event->load(['room', 'movedToRoom', 'movedByUser']);

        return view('guru.events.show', compact('event'));
    }

    private function sessionOrder(SchoolSession $session): int
    {
        return $session->sort_order ?: (int) str_replace('Sesi ', '', $session->name);
    }
}

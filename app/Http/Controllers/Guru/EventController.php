<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['room'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('guru.events.index', compact('events'));
    }

    public function create()
    {
        $rooms = Room::all();

        return view('guru.events.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'room_id' => 'required|exists:rooms,id',
            'date' => 'nullable|date|after_or_equal:today',
            'start_date' => 'required_without:date|date|after_or_equal:today',
            'end_date' => 'required_without:date|date|after_or_equal:start_date',
            'start_session' => 'required|in:Sesi 1,Sesi 2,Sesi 3,Sesi 4,Sesi 5,Sesi 6,Sesi 7,Sesi 8,Sesi 9,Sesi 10,Sesi 11',
            'end_session' => 'required|in:Sesi 1,Sesi 2,Sesi 3,Sesi 4,Sesi 5,Sesi 6,Sesi 7,Sesi 8,Sesi 9,Sesi 10,Sesi 11',
        ], [
            'date.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
            'end_time.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        $validated['start_date'] = $validated['start_date'] ?? $validated['date'];
        $validated['end_date'] = $validated['end_date'] ?? $validated['date'];
        $validated['date'] = $validated['start_date'];

        if ($this->sessionNumber($validated['end_session']) < $this->sessionNumber($validated['start_session'])) {
            throw ValidationException::withMessages([
                'end_session' => 'Sesi selesai harus sama atau setelah sesi mulai.',
            ]);
        }

        [$validated['start_time']] = $this->sessionTimes($validated['start_session']);
        [, $validated['end_time']] = $this->sessionTimes($validated['end_session']);
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

    private function sessionTimes(string $session): array
    {
        return match ($session) {
            'Sesi 1' => ['07:15', '08:00'],
            'Sesi 2' => ['08:00', '08:45'],
            'Sesi 3' => ['08:45', '09:30'],
            'Sesi 4' => ['09:30', '10:15'],
            'Sesi 5' => ['10:15', '11:00'],
            'Sesi 6' => ['11:00', '11:45'],
            'Sesi 7' => ['11:45', '12:30'],
            'Sesi 8' => ['12:30', '13:15'],
            'Sesi 9' => ['13:15', '14:00'],
            'Sesi 10' => ['14:00', '14:45'],
            'Sesi 11' => ['14:45', '15:30'],
        };
    }

    private function sessionNumber(string $session): int
    {
        return (int) str_replace('Sesi ', '', $session);
    }
}

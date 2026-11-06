<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventApiController extends Controller
{
    /**
     * GET /api/events
     * Daftar event publik (untuk Siswa/Tamu — kalender read-only)
     * GET /api/events?month=2026-10 — filter per bulan
     * GET /api/events?date=2026-10-20 — filter per tanggal
     */
    public function index(Request $request)
    {
        $request->validate([
            'month' => 'nullable|date_format:Y-m',
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $query = Event::with(['room:id,name,location', 'user:id,name'])
            ->whereIn('status', ['approved', 'moved'])
            ->orderBy('date')
            ->orderBy('start_time');

        if ($request->filled('month')) {
            $month = Carbon::createFromFormat('!Y-m', $request->month);

            $query->whereRaw('COALESCE(start_date, date) <= ?', [$month->copy()->endOfMonth()->toDateString()])
                ->whereRaw('COALESCE(end_date, date) >= ?', [$month->copy()->startOfMonth()->toDateString()]);
        }

        if ($request->filled('date')) {
            $query->whereRaw('COALESCE(start_date, date) <= ?', [$request->date])
                ->whereRaw('COALESCE(end_date, date) >= ?', [$request->date]);
        }

        $events = $query->get()->map(fn ($e) => $this->formatEvent($e));

        return response()->json([
            'data' => $events,
            'total' => $events->count(),
        ]);
    }

    /**
     * GET /api/events/my
     * Daftar event milik user yang sedang login (Guru)
     */
    public function myEvents(Request $request)
    {
        if (! in_array($request->user()->role, ['guru', 'organisasi'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $events = Event::with(['room:id,name,location', 'movedToRoom:id,name'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'data' => $events->map(fn ($e) => $this->formatEvent($e, true)),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    /**
     * GET /api/events/{id}
     * Detail event (publik)
     */
    public function show(Event $event)
    {
        // Hanya tampilkan yang approved/moved untuk publik
        if (! in_array($event->status, ['approved', 'moved'])) {
            // Jika ada user login, cek apakah pemilik
            if (! auth('sanctum')->check() || auth('sanctum')->id() !== $event->user_id) {
                return response()->json(['message' => 'Event tidak ditemukan.'], 404);
            }
        }

        $event->load(['room', 'user:id,name', 'movedToRoom:id,name', 'movedByUser:id,name']);

        return response()->json(['data' => $this->formatEvent($event, true)]);
    }

    /**
     * POST /api/events
     * Ajukan peminjaman ruang (Guru saja)
     */
    public function store(Request $request)
    {
        if (! in_array($request->user()->role, ['guru', 'organisasi'])) {
            return response()->json(['message' => 'Hanya Guru/Organisasi yang bisa mengajukan peminjaman.'], 403);
        }

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
        // LOGIKA BISNIS #1: CONFLICT PREVENTION
        // ====================================================
        $conflict = Event::overlapping(
            $validated['room_id'],
            $validated['start_date'],
            $validated['end_date'],
            $validated['start_time'],
            $validated['end_time'],
        )->first();

        if ($conflict) {
            return response()->json([
                'message' => 'Ruangan sudah dipesan pada waktu tersebut.',
                'conflict' => [
                    'title' => $conflict->title,
                    'start_date' => $conflict->bookingStartDate()->format('Y-m-d'),
                    'end_date' => $conflict->bookingEndDate()->format('Y-m-d'),
                    'start_time' => substr($conflict->start_time, 0, 5),
                    'end_time' => substr($conflict->end_time, 0, 5),
                ],
            ], 409); // HTTP 409 Conflict
        }

        $validated['user_id'] = $request->user()->id;
        $validated['status'] = 'pending';

        $event = Event::create($validated);
        $event->load('room:id,name');

        return response()->json([
            'message' => 'Pengajuan peminjaman berhasil dikirim! Menunggu persetujuan.',
            'data' => $this->formatEvent($event),
        ], 201);
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

    /**
     * GET /api/rooms/available
     * Cek ketersediaan ruangan pada tanggal & waktu tertentu
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'date' => 'nullable|date',
            'start_date' => 'required_without:date|date|after_or_equal:today',
            'end_date' => 'required_without:date|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $startDate = $request->input('start_date', $request->date);
        $endDate = $request->input('end_date', $request->date);

        $rooms = Room::all()->map(function ($room) use ($request) {
            $conflict = Event::overlapping(
                $room->id,
                $startDate,
                $endDate,
                $request->start_time,
                $request->end_time,
            )->exists();

            return [
                'id' => $room->id,
                'name' => $room->name,
                'location' => $room->location,
                'capacity' => $room->capacity,
                'available' => ! $conflict,
            ];
        });

        return response()->json(['data' => $rooms]);
    }

    // ============================================================
    // Helper: format event data for API response
    // ============================================================
    private function formatEvent(Event $event, bool $includeDetails = false): array
    {
        $data = [
            'id' => $event->id,
            'title' => $event->title,
            'room' => $event->room ? [
                'id' => $event->room->id,
                'name' => $event->room->name,
                'location' => $event->room->location ?? null,
            ] : null,
            'date' => $event->date ? $event->date->format('Y-m-d') : null,
            'start_date' => $event->bookingStartDate()->format('Y-m-d'),
            'end_date' => $event->bookingEndDate()->format('Y-m-d'),
            'duration_days' => $event->bookingDurationInDays(),
            'start_time' => $event->start_time ? substr($event->start_time, 0, 5) : null,
            'end_time' => $event->end_time ? substr($event->end_time, 0, 5) : null,
            'status' => $event->status,
            'session' => $event->session,
        ];

        if ($includeDetails) {
            $data['description'] = $event->description;
            $data['move_reason'] = $event->move_reason;
            $data['rejection_message'] = $event->rejection_message;
            $data['rejection_notes'] = $event->rejection_notes;
            $data['moved_to_room'] = $event->movedToRoom ? [
                'id' => $event->movedToRoom->id,
                'name' => $event->movedToRoom->name,
            ] : null;
            $data['moved_by'] = $event->movedByUser ? [
                'id' => $event->movedByUser->id,
                'name' => $event->movedByUser->name,
            ] : null;
            $data['organizer'] = $event->user ? $event->user->name : null;
            $data['created_at'] = $event->created_at?->toISOString();
        }

        return $data;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Room;
use App\Models\RoomNotification;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::with(['room', 'user'])->latest()->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $rooms = Room::all();

        return view('admin.events.create', compact('rooms'));
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

        // Conflict Prevention (sama dengan guru)
        $conflict = Event::overlapping(
            $validated['room_id'],
            $validated['start_date'],
            $validated['end_date'],
            $validated['start_time'],
            $validated['end_time'],
        )->first();

        if ($conflict) {
            return back()->withInput()
                ->with('conflict_error', "Ruangan sudah dipesan: {$conflict->title} ({$conflict->start_time}-{$conflict->end_time})");
        }

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'approved'; // Admin langsung approved

        Event::create($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Agenda berhasil ditambahkan!');
    }

    public function edit(Event $event)
    {
        $rooms = Room::all();

        return view('admin.events.edit', compact('event', 'rooms'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'rejection_message' => 'required_if:status,rejected|nullable|string|max:150',
            'rejection_notes' => 'required_if:status,rejected|nullable|string|max:1000',
        ]);
        if ($validated['status'] !== 'rejected') {
            $validated['rejection_message'] = null;
            $validated['rejection_notes'] = null;
        }

        if ($validated['status'] === 'approved') {
            $conflict = Event::overlapping(
                $event->room_id,
                $event->bookingStartDate()->toDateString(),
                $event->bookingEndDate()->toDateString(),
                $event->start_time,
                $event->end_time,
                $event->id,
            )->first();

            if ($conflict !== null) {
                return back()->with('error', 'Agenda tidak dapat disetujui karena ruangan bentrok pada rentang tanggal atau waktu tersebut.');
            }
        }

        $event->update($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Status event diperbarui!');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event dihapus!');
    }

    // ============================================================
    // LOGIKA BISNIS #5: OVERRIDE / PINDAH RUANG (WAJIB)
    // ============================================================
    public function moveRoom(Request $request, Event $event)
    {
        $validated = $request->validate([
            'new_room_id' => 'required|exists:rooms,id|different:room_id',
            'move_reason' => 'required|string|min:10|max:500',
        ], [
            'new_room_id.different' => 'Pilih ruangan yang berbeda dari ruangan saat ini.',
            'move_reason.min' => 'Alasan pemindahan minimal 10 karakter.',
        ]);

        $oldRoom = $event->room->name;
        $newRoom = Room::find($validated['new_room_id']);

        // Cek konflik di ruang baru
        $conflict = Event::overlapping(
            $validated['new_room_id'],
            $event->bookingStartDate()->toDateString(),
            $event->bookingEndDate()->toDateString(),
            $event->start_time,
            $event->end_time,
            $event->id,
        )->first();

        if ($conflict) {
            return back()->with('error', 'Ruangan baru juga sudah ada yang memesan pada waktu yang sama!');
        }

        // Simpan info lama
        $originalRoomId = $event->room_id;

        // Update event
        $event->update([
            'room_id' => $validated['new_room_id'],
            'move_reason' => $validated['move_reason'],
            'moved_to_room_id' => $validated['new_room_id'],
            'moved_by' => auth()->id(),
            'status' => 'moved',
        ]);

        // ============================================================
        // Kirim notifikasi otomatis ke pemilik event (Guru/Organisasi)
        // ============================================================
        RoomNotification::create([
            'user_id' => $event->user_id,
            'title' => '⚠️ Ruangan Kamu Dipindahkan',
            'message' => "Agenda \"{$event->title}\" ({$event->bookingStartDate()->format('d/m/Y')} - {$event->bookingEndDate()->format('d/m/Y')}) ".
                          "dipindahkan dari ruang **{$oldRoom}** ke ruang **{$newRoom->name}**. ".
                          "Alasan: {$validated['move_reason']}",
            'event_id' => $event->id,
            'is_read' => false,
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', "Ruangan berhasil dipindah ke {$newRoom->name}. Guru/Organisasi sudah mendapat notifikasi.");
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

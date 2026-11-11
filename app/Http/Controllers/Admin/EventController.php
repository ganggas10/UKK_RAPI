<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Room;
use App\Models\RoomNotification;
use App\Models\SchoolSession;
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
        $sessions = SchoolSession::active()->orderBy('sort_order')->orderBy('start_time')->get();

        return view('admin.events.create', compact('rooms', 'sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'room_id' => 'required|exists:rooms,id',
            'date' => 'nullable|date|after:today',
            'start_date' => 'required_without:date|date|after:today',
            'end_date' => 'required_without:date|date|after_or_equal:start_date',
            'start_session' => ['required', 'string', 'exists:school_sessions,name'],
            'end_session' => ['required', 'string', 'exists:school_sessions,name'],
        ], [
            'date.after' => 'Pengajuan peminjaman ruangan wajib dilakukan minimal 1 hari sebelum tanggal penggunaan. Silakan pilih tanggal penggunaan yang lain.',
            'start_date.after' => 'Pengajuan peminjaman ruangan wajib dilakukan minimal 1 hari sebelum tanggal penggunaan. Silakan pilih tanggal penggunaan yang lain.',
            'start_session.exists' => 'Sesi mulai yang dipilih tidak valid atau sudah tidak aktif.',
            'end_session.exists' => 'Sesi selesai yang dipilih tidak valid atau sudah tidak aktif.',
        ]);

        $validated['start_date'] = $validated['start_date'] ?? $validated['date'];
        $validated['end_date'] = $validated['end_date'] ?? $validated['date'];
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

    private function sessionOrder(SchoolSession $session): int
    {
        return $session->sort_order ?: (int) str_replace('Sesi ', '', $session->name);
    }
}

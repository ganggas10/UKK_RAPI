<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PicAssignment;
use App\Models\Room;
use App\Models\RoomNotification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PicAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Room::with(['pic', 'activePicAssignments.pic'])->orderBy('id');
        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($roomQuery) use ($search): void {
                $roomQuery->where('name', 'like', "%{$search}%")
                    ->orWhereHas('pic', fn ($picQuery) => $picQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('activePicAssignments.pic', fn ($picQuery) => $picQuery->where('name', 'like', "%{$search}%"));
            });
        }
        if ($request->input('status') === 'assigned') {
            $query->where(function ($q) {
                $q->whereNotNull('pic_id')->orWhereHas('activePicAssignments');
            });
        } elseif ($request->input('status') === 'unassigned') {
            $query->whereNull('pic_id')->whereDoesntHave('activePicAssignments');
        }
        if ($request->filled('pic_id')) {
            $picId = $request->integer('pic_id');
            $query->where(function ($q) use ($picId) {
                $q->where('pic_id', $picId)
                    ->orWhereHas('activePicAssignments', fn ($aq) => $aq->where('pic_id', $picId));
            });
        }
        if ($request->filled('category')) {
            $query->where('location', $request->string('category'));
        }

        $assignedCount = Room::whereNotNull('pic_id')->orWhereHas('activePicAssignments')->count();

        return view('admin.pic-assignments.index', [
            'rooms' => $query->paginate(15)->withQueryString(),
            'pics' => User::where('role', 'pic')->orderBy('name')->get(),
            'stats' => [
                'rooms' => Room::count(),
                'pics' => User::where('role', 'pic')->count(),
                'assigned' => $assignedCount,
                'unassigned' => max(0, Room::count() - $assignedCount),
            ],
            'categories' => Room::query()->whereNotNull('location')->distinct()->orderBy('location')->pluck('location'),
        ]);
    }

    public function create(): View
    {
        return view('admin.pic-assignments.create', [
            'rooms' => Room::orderBy('id')->get(),
            'pics' => User::where('role', 'pic')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'pic_id' => ['required', Rule::exists('users', 'id')->where('role', 'pic')],
            'starts_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $pic = User::whereKey($validated['pic_id'])->where('role', 'pic')->firstOrFail();

        DB::transaction(function () use ($validated, $pic, $request): void {
            $room = Room::whereKey($validated['room_id'])->lockForUpdate()->firstOrFail();
            $alreadyAssigned = PicAssignment::where('room_id', $room->id)
                ->where('pic_id', $pic->id)
                ->where('status', 'aktif')
                ->exists();
            abort_if($alreadyAssigned, 422, 'PIC tersebut sudah ditugaskan secara aktif pada ruangan ini.');

            $assignment = PicAssignment::create([
                ...$validated,
                'status' => 'aktif',
                'assigned_by' => $request->user()->id,
            ]);
            if (! $room->pic_id) {
                $room->update(['pic_id' => $pic->id]);
            }
            RoomNotification::create([
                'user_id' => $pic->id,
                'title' => 'Penugasan PIC baru',
                'message' => "Anda ditugaskan sebagai PIC {$room->name} mulai {$assignment->starts_at->format('d/m/Y')}.",
            ]);
        });

        return redirect()->route('admin.pic-assignments.index')->with('success', 'Penugasan PIC berhasil disimpan.');
    }

    public function show(PicAssignment $picAssignment): View
    {
        $picAssignment->load(['room.complaints', 'pic', 'assignedBy', 'room.picAssignments.pic', 'room.picAssignments.assignedBy']);
        $complaints = $picAssignment->room?->complaints ?? collect();

        return view('admin.pic-assignments.show', [
            'assignment' => $picAssignment,
            'complaints' => $complaints,
            'history' => $picAssignment->room?->picAssignments->sortByDesc('starts_at') ?? collect(),
        ]);
    }

    public function edit(PicAssignment $picAssignment): View
    {
        abort_if($picAssignment->status !== 'aktif', 404);

        return view('admin.pic-assignments.edit', [
            'assignment' => $picAssignment->load('room', 'pic'),
            'pics' => User::where('role', 'pic')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PicAssignment $picAssignment): RedirectResponse
    {
        abort_if($picAssignment->status !== 'aktif', 404);
        $validated = $request->validate([
            'pic_id' => ['required', Rule::exists('users', 'id')->where('role', 'pic')],
            'starts_at' => ['required', 'date'],
            'replacement_reason' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $newPic = User::whereKey($validated['pic_id'])->where('role', 'pic')->firstOrFail();

        DB::transaction(function () use ($validated, $newPic, $picAssignment, $request): void {
            $room = Room::whereKey($picAssignment->room_id)->lockForUpdate()->firstOrFail();
            $oldPic = $picAssignment->pic;
            $picAssignment->update([
                'status' => 'selesai',
                'ends_at' => $validated['starts_at'],
                'replacement_reason' => $validated['replacement_reason'],
            ]);
            $newAssignment = PicAssignment::create([
                'room_id' => $room->id,
                'pic_id' => $newPic->id,
                'starts_at' => $validated['starts_at'],
                'status' => 'aktif',
                'notes' => $validated['notes'] ?? null,
                'assigned_by' => $request->user()->id,
            ]);
            $room->update(['pic_id' => $newPic->id]);
            if ($oldPic) {
                RoomNotification::create([
                    'user_id' => $oldPic->id,
                    'title' => 'Penugasan PIC berakhir',
                    'message' => "Penugasan Anda untuk {$room->name} telah berakhir.",
                ]);
            }
            RoomNotification::create([
                'user_id' => $newPic->id,
                'title' => 'Penugasan PIC baru',
                'message' => "Anda ditugaskan sebagai PIC {$room->name} mulai {$newAssignment->starts_at->format('d/m/Y')}.",
            ]);
        });

        return redirect()->route('admin.pic-assignments.index')->with('success', 'PIC ruangan berhasil diganti dan riwayat disimpan.');
    }

    public function destroy(PicAssignment $picAssignment): RedirectResponse
    {
        abort_if($picAssignment->status !== 'aktif', 404);
        DB::transaction(function () use ($picAssignment): void {
            $room = Room::whereKey($picAssignment->room_id)->lockForUpdate()->firstOrFail();
            $picAssignment->update(['status' => 'selesai', 'ends_at' => now()->toDateString()]);
            $room->update(['pic_id' => null]);
        });

        return back()->with('success', 'Penugasan PIC berhasil dihentikan.');
    }

    public function history(): View
    {
        return view('admin.pic-assignments.history', [
            'assignments' => PicAssignment::with(['room', 'pic', 'assignedBy'])->latest()->paginate(20),
        ]);
    }
}

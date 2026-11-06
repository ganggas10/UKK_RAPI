<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgendaHistoryController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected', 'moved'];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:pending,approved,rejected,moved'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'sort' => ['nullable', 'in:submitted_desc,submitted_asc,date_desc,date_asc'],
        ], [
            'date_to.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $query = Event::query()->with([
            'room:id,name,location',
            'user:id,name,email,role,phone',
        ]);

        $search = $validated['search'] ?? null;
        if ($search) {
            $query->where(function ($eventQuery) use ($search) {
                $eventQuery->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $query->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['user_id'] ?? null, fn ($q, $userId) => $q->where('user_id', $userId));

        if (($validated['date_from'] ?? null) || ($validated['date_to'] ?? null)) {
            $query->where(function ($dateQuery) use ($validated): void {
                if ($validated['date_to'] ?? null) {
                    $dateQuery->whereRaw('COALESCE(start_date, date) <= ?', [$validated['date_to']]);
                }

                if ($validated['date_from'] ?? null) {
                    $dateQuery->whereRaw('COALESCE(end_date, date) >= ?', [$validated['date_from']]);
                }
            });
        }

        match ($validated['sort'] ?? 'submitted_desc') {
            'submitted_asc' => $query->oldest('created_at'),
            'date_desc' => $query->orderByDesc('start_date')->orderByDesc('date')->orderByDesc('start_time'),
            'date_asc' => $query->orderBy('start_date')->orderBy('date')->orderBy('start_time'),
            default => $query->latest('created_at'),
        };

        $events = $query->paginate(5)->withQueryString();
        $statusCounts = Event::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'total' => Event::count(),
            'completed' => 0,
            'rejected' => (int) ($statusCounts['rejected'] ?? 0),
            'cancelled' => 0,
            'processing' => (int) ($statusCounts['pending'] ?? 0),
        ];

        $applicants = User::query()
            ->whereHas('events')
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.agenda.history', [
            'events' => $events,
            'stats' => $stats,
            'applicants' => $applicants,
            'statuses' => self::STATUSES,
            'filters' => $validated,
        ]);
    }

    public function show(Event $event): View
    {
        $event->load([
            'room:id,name,location,capacity',
            'user:id,name,email,role,phone',
            'movedToRoom:id,name,location',
            'movedByUser:id,name,email,role',
        ]);

        return view('admin.agenda.detail', compact('event'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'period' => ['nullable', 'in:daily,monthly'],
            'date' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $period = $validated['period'] ?? 'daily';
        $date = Carbon::parse($validated['date'] ?? now()->toDateString());
        $month = Carbon::createFromFormat('!Y-m', $validated['month'] ?? now()->format('Y-m'));
        $from = $period === 'monthly' ? $month->copy()->startOfMonth()->toDateString() : $date->toDateString();
        $to = $period === 'monthly' ? $month->copy()->endOfMonth()->toDateString() : $from;

        $events = Event::with(['room:id,name', 'user:id,name,role'])
            ->whereRaw('COALESCE(start_date, date) <= ?', [$to])
            ->whereRaw('COALESCE(end_date, date) >= ?', [$from])
            ->orderBy('start_date')->orderBy('date')->orderBy('start_time')->get();
        $complaints = Complaint::with(['room:id,name', 'reporter:id,name,role'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$from, $to])->latest()->get();

        return view('admin.reports.index', [
            'period' => $period, 'date' => $date, 'month' => $month,
            'events' => $events, 'complaints' => $complaints,
            'eventStatus' => $events->countBy('status'),
            'complaintStatus' => $complaints->countBy('status_repair'),
        ]);
    }
}

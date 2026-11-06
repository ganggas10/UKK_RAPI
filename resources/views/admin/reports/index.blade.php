@extends('layouts.app')

@section('title', 'Laporan Peminjaman dan Pengaduan')

@section('content')
<div class="container-fluid py-4 px-3 px-lg-4">
    <h2 class="fw-bold mb-1">Laporan Peminjaman dan Pengaduan</h2>
    <p class="text-muted mb-4">Ringkasan aktivitas ruang berdasarkan periode yang dipilih.</p>

    <div class="card shadow-sm mb-4"><div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label fw-semibold">Periode</label><select name="period" class="form-select"><option value="daily" @selected($period === 'daily')>Harian</option><option value="monthly" @selected($period === 'monthly')>Bulanan</option></select></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Tanggal harian</label><input type="date" name="date" value="{{ $date->toDateString() }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Bulan bulanan</label><input type="month" name="month" value="{{ $month->format('Y-m') }}" class="form-control"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">Tampilkan</button></div>
        </form>
    </div></div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Peminjaman</small><h3 class="fw-bold mb-0">{{ $events->count() }}</h3></div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Pengaduan Kerusakan</small><h3 class="fw-bold mb-0">{{ $complaints->count() }}</h3></div></div></div>
        <div class="col-md-4"><div class="card shadow-sm"><div class="card-body"><small class="text-muted">Belum Diperbaiki</small><h3 class="fw-bold text-danger mb-0">{{ $complaintStatus['Belum Diperbaiki'] ?? 0 }}</h3></div></div></div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7"><div class="card shadow-sm"><div class="card-header bg-white fw-semibold">Rincian Peminjaman</div><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-dark"><tr><th>Periode</th><th>Agenda</th><th>Ruangan</th><th>Sesi</th><th>Status</th></tr></thead><tbody>@forelse($events as $event)<tr><td>{{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }}<br><small class="text-muted">{{ $event->bookingDurationInDays() }} hari</small></td><td>{{ $event->title }}</td><td>{{ $event->room?->name }}</td><td>{{ $event->session ?: substr($event->start_time,0,5).'–'.substr($event->end_time,0,5) }}</td><td>{{ ucfirst($event->status) }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Tidak ada peminjaman pada periode ini.</td></tr>@endforelse</tbody></table></div></div></div>
        <div class="col-lg-5"><div class="card shadow-sm"><div class="card-header bg-white fw-semibold">Rincian Pengaduan</div><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-dark"><tr><th>Tanggal</th><th>Ruangan</th><th>Status</th></tr></thead><tbody>@forelse($complaints as $complaint)<tr><td>{{ $complaint->created_at->format('d/m/Y') }}</td><td>{{ $complaint->room?->name }}</td><td>{{ $complaint->status_repair }}</td></tr>@empty<tr><td colspan="3" class="text-center text-muted py-4">Tidak ada pengaduan pada periode ini.</td></tr>@endforelse</tbody></table></div></div></div>
    </div>
</div>
@endsection
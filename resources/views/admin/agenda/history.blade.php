@extends('layouts.app')

@section('title', 'Riwayat Agenda')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Menunggu Persetujuan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'moved' => 'Dipindahkan',
    ];
    $statusBadges = [
        'pending' => 'warning',
        'approved' => 'primary',
        'rejected' => 'danger',
        'moved' => 'info',
    ];
@endphp

<div class="container-fluid py-4 px-3 px-lg-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">MONITORING WAKA / ADMIN</p>
            <h2 class="fw-bold mb-1">Riwayat Agenda</h2>
            <p class="text-muted mb-0">Pantau seluruh riwayat pengajuan dan pelaksanaan agenda sekolah dalam satu halaman.</p>
        </div>
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-primary">Manajemen Agenda</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl">
            <div class="rapi-asset-card pastel-blue">
                <div>
                    <div class="rapi-asset-value">{{ number_format($stats['total']) }}</div>
                    <div class="rapi-asset-label">Total Agenda</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="rapi-asset-card pastel-green">
                <div>
                    <div class="rapi-asset-value text-success">{{ number_format($stats['completed']) }}</div>
                    <div class="rapi-asset-label">Agenda Selesai</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="rapi-asset-card pastel-rose">
                <div>
                    <div class="rapi-asset-value text-danger">{{ number_format($stats['rejected']) }}</div>
                    <div class="rapi-asset-label">Agenda Ditolak</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl">
            <div class="rapi-asset-card pastel-purple">
                <div>
                    <div class="rapi-asset-value text-secondary">{{ number_format($stats['cancelled']) }}</div>
                    <div class="rapi-asset-label">Agenda Dibatalkan</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl">
            <div class="rapi-asset-card pastel-yellow">
                <div>
                    <div class="rapi-asset-value text-warning">{{ number_format($stats['processing']) }}</div>
                    <div class="rapi-asset-label">Agenda Diproses</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('waka.agenda.history') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4">
                        <label for="search" class="form-label fw-semibold">Cari Agenda</label>
                        <input id="search" type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Cari nama agenda, pemohon, atau ruangan...">
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label for="status" class="form-label fw-semibold">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $statusLabels[$status] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <label for="user_id" class="form-label fw-semibold">Pemohon</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">Semua Pemohon</option>
                            @foreach($applicants as $applicant)
                                <option value="{{ $applicant->id }}" @selected((string) ($filters['user_id'] ?? '') === (string) $applicant->id)>{{ $applicant->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-6 col-lg-2"><label for="date_from" class="form-label fw-semibold">Tanggal Mulai</label><input id="date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control"></div>
                    <div class="col-sm-6 col-lg-2"><label for="date_to" class="form-label fw-semibold">Tanggal Selesai</label><input id="date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control"></div>
                    <div class="col-sm-6 col-lg-2"><label for="sort" class="form-label fw-semibold">Urutkan</label><select id="sort" name="sort" class="form-select"><option value="submitted_desc" @selected(($filters['sort'] ?? 'submitted_desc') === 'submitted_desc')>Pengajuan terbaru</option><option value="submitted_asc" @selected(($filters['sort'] ?? '') === 'submitted_asc')>Pengajuan terlama</option><option value="date_desc" @selected(($filters['sort'] ?? '') === 'date_desc')>Pelaksanaan terbaru</option><option value="date_asc" @selected(($filters['sort'] ?? '') === 'date_asc')>Pelaksanaan terlama</option></select></div>
                    <div class="col-sm-6 col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1" type="submit">Filter</button><a class="btn btn-outline-secondary" href="{{ route('waka.agenda.history') }}" title="Reset filter">Reset</a></div>
                </div>
            </form>
            @if($errors->any())<div class="alert alert-danger mt-3 mb-0">{{ $errors->first() }}</div>@endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body border-bottom"><strong>{{ number_format($events->total()) }}</strong> agenda ditemukan</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark"><tr><th>No.</th><th>Kode Agenda</th><th>Nama Agenda</th><th>Pemohon</th><th>Role</th><th>Ruangan</th><th>Tanggal</th><th>Waktu</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @forelse($events as $event)
                    <tr>
                        <td>{{ $events->firstItem() + $loop->index }}</td>
                        <td><span class="text-muted">#{{ $event->id }}</span></td>
                        <td><strong>{{ $event->title }}</strong></td>
                        <td>{{ $event->user?->name ?? 'Tidak tersedia' }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $event->creatorColor() }}; color: #ffffff;">
                                {{ $event->creatorRoleLabel() }}
                            </span>
                        </td>
                        <td>{{ $event->room?->name ?? 'Tidak tersedia' }}</td>
                        <td>{{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }}<br><small class="text-muted">{{ $event->bookingDurationInDays() }} hari</small></td>
                        <td>{{ substr($event->start_time, 0, 5) }}–{{ substr($event->end_time, 0, 5) }}</td>
                        <td><span class="badge text-bg-{{ $statusBadges[$event->status] ?? 'secondary' }}">{{ $statusLabels[$event->status] ?? ucfirst($event->status) }}</span></td>
                        <td><a href="{{ route('waka.agenda.detail', $event) }}" class="btn btn-sm btn-outline-primary">Lihat Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center py-5"><p class="text-muted mb-0 mt-2">Belum ada agenda yang sesuai filter.</p></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($events->hasPages())
            <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Menampilkan {{ $events->firstItem() }}–{{ $events->lastItem() }} dari {{ $events->total() }} agenda
                </small>
                <div class="agenda-history-pagination">{{ $events->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .agenda-history-pagination .pagination { margin: 0; gap: 0.35rem; }
    .agenda-history-pagination .page-item .page-link {
        min-width: 38px;
        padding: 0.45rem 0.7rem;
        color: #1e40af;
        background: #fff;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        text-align: center;
        font-weight: 700;
        transition: color 0.2s ease, background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .agenda-history-pagination .page-item:not(.disabled) .page-link:hover {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.2);
        transform: translateY(-1px);
    }
    .agenda-history-pagination .page-item.active .page-link {
        color: #fff;
        background: linear-gradient(135deg, #1e40af, #2563eb);
        border-color: #2563eb;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.22);
    }
    .agenda-history-pagination .page-item.disabled .page-link {
        color: #94a3b8;
        background: #f8fafc;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }
    @media (max-width: 576px) {
        .agenda-history-pagination { width: 100%; overflow-x: auto; padding-bottom: 0.15rem; }
        .agenda-history-pagination .pagination { width: max-content; }
    }
</style>
@endpush
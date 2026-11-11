@extends('layouts.app')

@section('title', 'Peminjaman Saya')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Peminjaman Ruang Saya</h2>
        <a href="{{ route('guru.events.create') }}" class="btn btn-primary">
            Ajukan Baru
        </a>
    </div>

    {{-- Area Filter Data Peminjaman --}}
    <div class="card shadow-sm border mb-4 rounded-3 bg-white">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('guru.events.index') }}">
                <div class="row g-3 align-items-end">
                    {{-- 1. Pencarian Judul --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="search" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-search me-1"></i>Pencarian
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="search" id="search" name="search" class="form-control border-start-0"
                                   placeholder="Cari judul peminjaman..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    {{-- 2. Filter Status --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="status" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-tag me-1"></i>Status
                        </label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending (Menunggu)</option>
                            <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Disetujui</option>
                            <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Ditolak</option>
                            <option value="moved" @selected(($filters['status'] ?? '') === 'moved')>Dipindah</option>
                        </select>
                    </div>

                    {{-- 3. Filter Ruangan --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="room_id" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-door-open me-1"></i>Ruangan
                        </label>
                        <select id="room_id" name="room_id" class="form-select">
                            <option value="">Semua Ruangan</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected((string)($filters['room_id'] ?? '') === (string)$room->id)>
                                    {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 4. Filter Tanggal Mulai --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="date_from" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-calendar-event me-1"></i>Tanggal Mulai
                        </label>
                        <input type="date" id="date_from" name="date_from" class="form-control"
                               value="{{ $filters['date_from'] ?? '' }}">
                    </div>

                    {{-- 5. Filter Tanggal Selesai --}}
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="date_to" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-calendar-check me-1"></i>Tanggal Selesai
                        </label>
                        <input type="date" id="date_to" name="date_to" class="form-control"
                               value="{{ $filters['date_to'] ?? '' }}">
                    </div>

                    {{-- 6. Tombol Aksi Terapkan & Reset --}}
                    <div class="col-12 col-lg-auto d-flex gap-2 ms-lg-auto">
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1 flex-lg-grow-0" title="Terapkan Filter">
                            <i class="bi bi-funnel-fill"></i> Terapkan
                        </button>
                        <a href="{{ route('guru.events.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1 flex-lg-grow-0" title="Reset Seluruh Filter">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            @if($errors->any())
                <div class="alert alert-danger mt-3 mb-0 py-2">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first() }}
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <span class="text-muted small">Ditemukan <strong>{{ $events->total() }}</strong> data peminjaman</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>Judul</th>
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr>
                        <td>
                            <strong>{{ $event->title }}</strong>
                            <div>
                                <span class="badge" style="background-color: {{ $event->creatorColor() }}; color: #ffffff; font-size: 0.72rem;">
                                    {{ $event->creatorRoleLabel() }}
                                </span>
                            </div>
                        </td>
                        <td>
                            {{ $event->room->name }}
                            @if($event->status === 'moved')
                                <br><small class="text-info">→ Dipindah ke: {{ $event->movedToRoom->name ?? '-' }}</small>
                            @endif
                        </td>
                        <td>{{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }}<br><small class="text-muted">{{ $event->bookingDurationInDays() }} hari</small></td>
                        <td>{{ $event->session ?: substr($event->start_time,0,5).'–'.substr($event->end_time,0,5) }}</td>
                        <td>
                            @php
                                $badge = match($event->status) {
                                    'approved' => 'success',
                                    'pending'  => 'warning text-dark',
                                    'rejected' => 'danger',
                                    'moved'    => 'info',
                                    default    => 'secondary'
                                };
                                $label = match($event->status) {
                                    'approved' => 'Disetujui',
                                    'pending'  => 'Menunggu',
                                    'rejected' => 'Ditolak',
                                    'moved'    => 'Dipindah',
                                    default    => 'Unknown'
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                        </td>
                        <td>
                            @if($event->move_reason)
                                <small class="text-warning">{{ Str::limit($event->move_reason, 50) }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('guru.events.show', $event) }}" class="btn btn-xs btn-outline-primary">
                                Detail
                            </a>
                            <a href="{{ route('guru.complaints.create', ['room_id' => $event->room_id]) }}" class="btn btn-xs btn-outline-danger">
                                Lapor Kerusakan
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pengajuan yang sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($events->hasPages())
        <div class="mt-4">
            {{ $events->links() }}
        </div>
    @endif
</div>
@endsection

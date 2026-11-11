@extends('layouts.app')

@section('title', 'Manajemen Agenda')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Manajemen Agenda & Peminjaman</h2>
            <p class="text-muted small mb-0">Kelola persetujuan, penolakan, dan pemindahan agenda peminjaman ruangan sekolah.</p>
        </div>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Agenda
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Area Filter Data Agenda & Peminjaman --}}
    <div class="card shadow-sm border mb-4 rounded-3 bg-white">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('admin.events.index') }}">
                <div class="row g-3 align-items-end">
                    {{-- 1. Pencarian Judul Agenda atau Nama Pengaju --}}
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="search" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-search me-1"></i>Pencarian
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="search" id="search" name="search" class="form-control border-start-0"
                                   placeholder="Judul agenda atau nama pengaju..." value="{{ $filters['search'] ?? '' }}">
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

                    {{-- 4. Filter Pengaju --}}
                    <div class="col-12 col-md-4 col-lg-2">
                        <label for="user_id" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-person me-1"></i>Pengaju
                        </label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">Semua Pengaju</option>
                            @foreach($applicants as $applicant)
                                <option value="{{ $applicant->id }}" @selected((string)($filters['user_id'] ?? '') === (string)$applicant->id)>
                                    {{ $applicant->name }} ({{ ucfirst($applicant->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 5. Filter Tanggal Mulai --}}
                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="date_from" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-calendar-event me-1"></i>Tanggal Mulai
                        </label>
                        <input type="date" id="date_from" name="date_from" class="form-control"
                               value="{{ $filters['date_from'] ?? '' }}">
                    </div>

                    {{-- 6. Filter Tanggal Selesai --}}
                    <div class="col-6 col-md-4 col-lg-2">
                        <label for="date_to" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-calendar-check me-1"></i>Tanggal Selesai
                        </label>
                        <input type="date" id="date_to" name="date_to" class="form-control"
                               value="{{ $filters['date_to'] ?? '' }}">
                    </div>

                    {{-- 7. Tombol Aksi Terapkan & Reset --}}
                    <div class="col-12 col-lg-auto d-flex gap-2 ms-lg-auto">
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1 flex-lg-grow-0" title="Terapkan Filter">
                            <i class="bi bi-funnel-fill"></i> Terapkan
                        </button>
                        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-1 flex-grow-1 flex-lg-grow-0" title="Reset Seluruh Filter">
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
            <span class="text-muted small">Ditemukan <strong>{{ $events->total() }}</strong> data agenda</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Judul</th>
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Pengaju</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr>
                        <td><strong>{{ $event->title }}</strong></td>
                        <td>
                            {{ $event->room->name }}
                            @if($event->status === 'moved')
                                <br><small class="text-info">→ Dipindah ke: {{ $event->movedToRoom->name ?? '-' }}</small>
                            @endif
                        </td>
                        <td>{{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }}<br><small class="text-muted">{{ $event->bookingDurationInDays() }} hari</small></td>
                        <td>{{ $event->session ?: substr($event->start_time,0,5).'–'.substr($event->end_time,0,5) }}</td>
                        <td>
                            <div>{{ $event->user->name }}</div>
                            <span class="badge" style="background-color: {{ $event->creatorColor() }}; color: #ffffff;">
                                {{ $event->creatorRoleLabel() }}
                            </span>
                        </td>
                        <td>
                            @php
                                $badge = match($event->status) {
                                    'approved' => 'success',
                                    'pending'  => 'warning',
                                    'rejected' => 'danger',
                                    'moved'    => 'info',
                                    default    => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ ucfirst($event->status) }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                {{-- Setujui/Tolak untuk event pending --}}
                                @if($event->status === 'pending')
                                <form action="{{ route('admin.events.update', $event) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-xs btn-success">Setujui</button>
                                </form>
                                <form action="{{ route('admin.events.update', $event) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="text" name="rejection_message" class="form-control form-control-sm mb-1" placeholder="Pesan penolakan" required>
                                    <textarea name="rejection_notes" class="form-control form-control-sm mb-1" rows="2" placeholder="Catatan/alasan penolakan" required></textarea>
                                    <button class="btn btn-xs btn-danger">Tolak</button>
                                </form>
                                @endif
                                {{-- Pindah Ruang (Override) --}}
                                @if(in_array($event->status, ['pending', 'approved']))
                                <button class="btn btn-xs btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#moveModal{{ $event->id }}">
                                    Pindah
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada agenda yang sesuai filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal diletakkan di luar tabel agar struktur HTML dan stacking Bootstrap valid. --}}
    @foreach($events as $event)
        @if(in_array($event->status, ['pending', 'approved']))
        <div class="modal fade" id="moveModal{{ $event->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Pindah Ruangan: {{ $event->title }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.events.move', $event) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted">Ruangan saat ini: <strong>{{ $event->room->name }}</strong></p>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pindah ke Ruangan <span class="text-danger">*</span></label>
                                <select name="new_room_id" class="form-select" required>
                                    <option value="">-- Pilih Ruangan Baru --</option>
                                    @foreach(\App\Models\Room::where('id', '!=', $event->room_id)->orderBy('name')->get() as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Alasan Pemindahan <span class="text-danger">*</span></label>
                                <textarea name="move_reason" class="form-control" rows="3"
                                    placeholder="Contoh: Ada rapat mendadak kepala sekolah di ruang tersebut..." required minlength="10"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning">Pindahkan & Kirim Notifikasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach

    <div class="mt-4">{{ $events->links() }}</div>
</div>
@endsection

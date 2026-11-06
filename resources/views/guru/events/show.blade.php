@extends('layouts.app')

@section('title', 'Detail Peminjaman')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('guru.events.index') }}" class="btn btn-outline-secondary btn-sm">
            Kembali
        </a>
        <h2 class="fw-bold mb-0">Detail Peminjaman Ruang</h2>
    </div>

    {{-- Status Badge --}}
    @php
        $badgeMap = [
            'approved' => ['class' => 'bg-success', 'label' => 'Disetujui'],
            'pending'  => ['class' => 'bg-warning text-dark', 'label' => '⏳ Menunggu Persetujuan'],
            'rejected' => ['class' => 'bg-danger', 'label' => 'Ditolak'],
            'moved'    => ['class' => 'bg-info', 'label' => 'Ruangan Dipindahkan'],
        ];
        $badge = $badgeMap[$event->status] ?? ['class' => 'bg-secondary', 'label' => $event->status];
    @endphp

    {{-- Notifikasi Pemindahan Ruang --}}
    @if($event->status === 'moved')
    <div class="alert alert-warning border-warning shadow-sm mb-4">
        <h6 class="alert-heading fw-bold">Ruangan Kamu Dipindahkan oleh Admin/Waka</h6>
        <hr>
        <div class="row g-2">
            <div class="col-6">
                <small class="text-muted d-block">Ruangan Lama</small>
                <strong class="text-danger">{{ $event->room->name }}</strong>
            </div>
            <div class="col-6">
                <small class="text-muted d-block">Ruangan Baru</small>
                <strong class="text-success">{{ optional($event->movedToRoom)->name ?? '-' }}</strong>
            </div>
        </div>
        @if($event->move_reason)
        <div class="mt-2">
            <small class="text-muted d-block">Alasan Pemindahan</small>
            <p class="mb-0">{{ $event->move_reason }}</p>
        </div>
        @endif
        @if($event->movedByUser)
        <small class="text-muted">Dipindahkan oleh: <strong>{{ $event->movedByUser->name }}</strong></small>
        @endif
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <strong>{{ $event->title }}</strong>
                <span class="badge {{ $badge['class'] }}">{{ $badge['label'] }}</span>
            </div>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Ruangan</dt>
                <dd class="col-sm-8">
                    {{ $event->room->name }}
                    @if($event->room->location)
                        <small class="text-muted">({{ $event->room->location }})</small>
                    @endif
                </dd>

                <dt class="col-sm-4 text-muted">Tanggal Mulai</dt>
                <dd class="col-sm-8">
                    {{ $event->bookingStartDate()->format('l, d F Y') }}
                </dd>

                <dt class="col-sm-4 text-muted">Tanggal Selesai</dt>
                <dd class="col-sm-8">{{ $event->bookingEndDate()->format('l, d F Y') }}</dd>

                <dt class="col-sm-4 text-muted">Durasi</dt>
                <dd class="col-sm-8">{{ $event->bookingDurationInDays() }} hari</dd>

                <dt class="col-sm-4 text-muted">Waktu</dt>
                <dd class="col-sm-8">
                    {{ $event->session ?: substr($event->start_time, 0, 5).' – '.substr($event->end_time, 0, 5).' WIB' }}
                </dd>

                @if($event->status === 'rejected')
                <dt class="col-sm-4 text-muted">Pesan Penolakan</dt>
                <dd class="col-sm-8">{{ $event->rejection_message ?: 'Tidak tersedia' }}</dd>
                <dt class="col-sm-4 text-muted">Catatan Penolakan</dt>
                <dd class="col-sm-8">{{ $event->rejection_notes ?: 'Tidak tersedia' }}</dd>
                @endif

                @if($event->description)
                <dt class="col-sm-4 text-muted">Deskripsi</dt>
                <dd class="col-sm-8">{{ $event->description }}</dd>
                @endif

                <dt class="col-sm-4 text-muted">Pengaju</dt>
                <dd class="col-sm-8">
                    {{ $event->user->name }}
                    <span class="badge ms-1" style="background-color: {{ $event->creatorColor() }}; color: #ffffff;">{{ $event->creatorRoleLabel() }}</span>
                </dd>

                <dt class="col-sm-4 text-muted">Dibuat</dt>
                <dd class="col-sm-8">{{ $event->created_at->format('d/m/Y H:i') }}</dd>
            </dl>
        </div>

        @if($event->status === 'moved' && $event->movedToRoom)
        <div class="card-footer bg-info bg-opacity-10">
            <small class="text-info fw-semibold">
                Sekarang menggunakan: <strong>{{ $event->movedToRoom->name }}</strong>
            </small>
        </div>
        @endif
        <a href="{{ route('guru.complaints.create', ['room_id' => $event->room_id]) }}" class="btn btn-outline-danger ms-2">Lapor Kerusakan</a>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('guru.events.index') }}" class="btn btn-outline-primary">
            Semua Peminjaman
        </a>
        @if($event->status === 'pending' || $event->status === 'approved')
        <a href="{{ route('calendar.public') }}" class="btn btn-outline-secondary ms-2">
            Lihat Kalender
        </a>
        @endif
    </div>
</div>
@endsection

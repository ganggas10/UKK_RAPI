@extends('layouts.app')

@section('title', 'Dashboard Guru & Organisasi')

@section('content')
<div class="container-fluid">
    {{-- NOTIFIKASI PEMINDAHAN RUANG --}}
    @if($notifications->count() > 0)
    <div class="mb-4">
        @foreach($notifications as $notif)
        <div class="alert alert-warning alert-dismissible border-warning shadow-sm" role="alert">
            <h6 class="alert-heading fw-bold">{{ $notif->title }}</h6>
            <p class="mb-2">{{ $notif->message }}</p>
            <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
            <div class="mt-2">
                <form action="{{ route('guru.notifications.read', $notif) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning">Tandai Sudah Dibaca</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- BARIS ATAS: PANEL AKSI CEPAT (KIRI) & KARTU GELAP KALENDER (KANAN) --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-7">
            <div class="rapi-balance-card">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="rapi-balance-value">Halo, {{ auth()->user()->name }}</div>
                            <div class="text-muted small fw-semibold mt-1">Ajukan peminjaman ruang kegiatan atau laporkan kendala fasilitas sekolah</div>
                        </div>
                        <span class="rapi-balance-pill">{{ strtoupper(auth()->user()->role) }}</span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-4 mt-2 border-top border-primary border-opacity-10">
                    <a href="{{ route('guru.events.create') }}" class="btn btn-primary px-4">
                        Ajukan Pinjam Ruang
                    </a>
                    <a href="{{ route('guru.complaints.create') }}" class="btn btn-danger px-4">
                        Lapor Kerusakan
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="rapi-dark-card">
                <div>
                    <span class="badge bg-primary mb-2">Jadwal Sekolah</span>
                    <h4>
                        Cek <span class="pill-highlight">Kalender</span> Ketersediaan Ruang
                    </h4>
                    <p class="text-white-50 small mb-3">
                        Pastikan ruangan yang akan dipinjam belum terjadwal pada jam dan tanggal yang sama.
                    </p>
                </div>
                <a href="{{ route('calendar.public') }}" class="btn-light-blue">
                    Buka Kalender Publik →
                </a>
            </div>
        </div>
    </div>

    {{-- Peminjaman Terbaru --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Peminjaman Terbaru Saya</span>
            <a href="{{ route('guru.events.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="card-body py-2">
            @forelse($myEvents as $event)
                @php
                    $badge = match($event->status) {
                        'approved' => 'success', 'pending'=> 'warning text-dark',
                        'rejected' => 'danger', 'moved'  => 'info', default => 'secondary'
                    };
                    $label = match($event->status) {
                        'approved' => 'Disetujui', 'pending' => 'Menunggu',
                        'rejected' => 'Ditolak',  'moved'   => 'Dipindah', default => '-'
                    };
                @endphp
                <div class="rapi-list-row">
                    <div class="d-flex align-items-center gap-3">
                        <span class="rapi-row-icon">
                            <i class="bi bi-calendar2-check"></i>
                        </span>
                        <div>
                            <div class="fw-bold text-dark">{{ $event->title }}</div>
                            <small class="text-muted">{{ $event->room->name }} · {{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }} · {{ substr($event->start_time,0,5) }}–{{ substr($event->end_time,0,5) }}</small>
                        </div>
                    </div>
                    <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                </div>
            @empty
                <div class="text-center text-muted py-4">Belum ada peminjaman.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

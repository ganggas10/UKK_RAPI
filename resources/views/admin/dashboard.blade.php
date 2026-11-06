@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-1">Dashboard Waka / Admin</h2>
    <p class="text-muted mb-4">Selamat datang, {{ auth()->user()->name }}</p>

    {{-- Statistik Kartu --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-white bg-primary shadow border-0">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0">{{ $stats['total_rooms'] }}</h3>
                    <small>Total Ruangan</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-white bg-info shadow border-0">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0">{{ $stats['total_users'] }}</h3>
                    <small>Total Pengguna</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-dark bg-warning shadow border-0">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0">{{ $stats['pending_events'] }}</h3>
                    <small>Event Menunggu</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-white bg-danger shadow border-0">
                <div class="card-body text-center py-3">
                    <h3 class="fw-bold mb-0">{{ $stats['open_complaints'] }}</h3>
                    <small>Kerusakan Terbuka</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Event Terbaru --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-semibold d-flex justify-content-between">
                    Agenda Terbaru
                    <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($recent_events as $event)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ $event->title }}</strong>
                                <br><small class="text-muted">{{ $event->room->name }} | {{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }}</small>
                            </div>
                            @php
                                $badge = match($event->status) {
                                    'approved' => 'success', 'pending' => 'warning text-dark',
                                    'rejected' => 'danger',  'moved'   => 'info',
                                    default    => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }} align-self-center">{{ ucfirst($event->status) }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-muted text-center py-3">Tidak ada agenda.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Pengaduan Belum Ditangani --}}
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-semibold d-flex justify-content-between">
                    Kerusakan Belum Ditangani
                    <a href="{{ route('admin.complaints.whatsapp', 0) }}" class="d-none">dummy</a>
                </div>
                <div class="list-group list-group-flush">
                    @forelse($open_complaints as $complaint)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            @if($complaint->photo_path)
                            <img src="{{ Storage::url($complaint->photo_path) }}"
                                 alt="Foto bukti kerusakan"
                                 class="rounded me-3 flex-shrink-0"
                                 style="width:64px;height:64px;object-fit:cover;"
                                 onerror="this.style.display='none'">
                            @endif
                            <div>
                                <strong>{{ $complaint->room->name }}</strong>
                                <br><small class="text-muted">{{ Str::limit($complaint->description, 60) }}</small>
                                <br><small class="text-danger">Lapor: {{ $complaint->reporter->name }}</small>
                            </div>
                            {{-- ============================================================
                                 LOGIKA BISNIS #4: TOMBOL WHATSAPP KE PIC (ESKALASI)
                                 Kirim pesan pengingat via WA ke PIC ruang ybs
                                 ============================================================ --}}
                            @if($complaint->room->pic && $complaint->room->pic->phone)
                            <a href="{{ route('admin.complaints.whatsapp', $complaint) }}"
                               class="btn btn-sm btn-success" target="_blank"
                               title="Kirim peringatan WhatsApp ke {{ $complaint->room->pic->name }}">
                                WA PIC
                            </a>
                            @else
                            <span class="badge bg-secondary">No. WA PIC kosong</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="list-group-item text-success text-center py-3">Semua kerusakan sudah ditangani!</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
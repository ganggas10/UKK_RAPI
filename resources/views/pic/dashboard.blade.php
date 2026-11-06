@extends('layouts.app')

@section('title', 'Dashboard PIC')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-1">Dashboard PIC Ruang</h2>
    <p class="text-muted mb-4">Selamat datang, {{ auth()->user()->name }}</p>

    {{-- Statistik --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm bg-primary text-white">
                <div class="card-body py-3">
                    <h3 class="fw-bold mb-0">{{ $stats['total_rooms'] }}</h3>
                    <small>Ruangan Saya</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm bg-danger text-white">
                <div class="card-body py-3">
                    <h3 class="fw-bold mb-0">{{ $stats['belum_diperbaiki'] }}</h3>
                    <small>Belum Diperbaiki</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm bg-warning text-dark">
                <div class="card-body py-3">
                    <h3 class="fw-bold mb-0">{{ $stats['menunggu_sparepart'] }}</h3>
                    <small>Menunggu Spare Part</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center border-0 shadow-sm bg-success text-white">
                <div class="card-body py-3">
                    <h3 class="fw-bold mb-0">{{ $stats['sudah_diperbaiki'] }}</h3>
                    <small>Sudah Diperbaiki</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Ruangan Tanggung Jawab --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold bg-light">Ruangan Tanggung Jawab Saya</div>
        <div class="card-body"><div class="row g-3">
            @forelse($myRooms as $room)
                <div class="col-md-6 col-xl-4"><div class="border rounded p-3 h-100"><h5 class="mb-1">{{ $room->name }}</h5><p class="text-muted small mb-2">{{ $room->location ?: 'Lokasi tidak tersedia' }}</p><p class="mb-0">Laporan kerusakan: <strong>{{ $room->complaints_count }}</strong></p><p class="mb-0">Belum ditangani: <strong>{{ $room->open_complaints_count }}</strong></p></div></div>
            @empty
                <div class="col-12 text-muted">Belum ada ruangan yang ditugaskan.</div>
            @endforelse
        </div></div>
    </div>

    {{-- Pengaduan Terbaru --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-light">
            <span class="fw-semibold">Pengaduan Terbaru</span>
            <a href="{{ route('pic.complaints.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="list-group list-group-flush">
            @forelse($recent_complaints as $complaint)
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    @if($complaint->photo_path)
                    <img src="{{ Storage::url($complaint->photo_path) }}"
                         alt="Foto bukti kerusakan"
                         class="rounded me-3 flex-shrink-0"
                         style="width:72px;height:72px;object-fit:cover;"
                         onerror="this.style.display='none'">
                    @else
                    <div class="rounded me-3 flex-shrink-0 bg-light text-muted d-flex align-items-center justify-content-center"
                         style="width:72px;height:72px;" title="Foto tidak tersedia">
                        Foto tidak tersedia
                    </div>
                    @endif
                    <div>
                        <strong>{{ $complaint->room->name }}</strong>
                        <p class="mb-1 text-muted small">{{ Str::limit($complaint->description, 80) }}</p>
                        <small class="text-muted">Dilaporkan oleh: {{ $complaint->reporter->name }} | {{ $complaint->created_at->diffForHumans() }}</small>
                    </div>
                    @php
                        $badge = match($complaint->status_repair) {
                            'Sudah Diperbaiki' => 'success',
                            'Menunggu Spare Part' => 'warning text-dark',
                            default => 'danger'
                        };
                    @endphp
                    <span class="badge bg-{{ $badge }}">{{ $complaint->status_repair }}</span>
                </div>
            </div>
            @empty
            <div class="list-group-item text-center text-muted py-4">Belum ada pengaduan</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
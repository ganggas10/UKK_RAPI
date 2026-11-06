@extends('layouts.app')

@section('title', 'Dashboard PIC Ruang')

@section('content')
<div class="container-fluid">
    {{-- BARIS ATAS: IKHTISAR RUANGAN SAYA (KIRI) & 3 KARTU PASTEL STATUS (KANAN) --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Tanggung Jawab PIC</h5>
                <span class="text-muted small">Halo, {{ auth()->user()->name }}</span>
            </div>

            <div class="rapi-balance-card">
                <div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="rapi-balance-value">{{ $stats['total_rooms'] }} Ruangan</div>
                            <div class="text-muted small fw-semibold mt-1">Total ruangan dalam tanggung jawab Anda</div>
                        </div>
                        <span class="rapi-balance-pill">
                            PIC Aktif
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-4 mt-2 border-top border-primary border-opacity-10">
                    <a href="{{ route('pic.complaints.index') }}" class="btn btn-primary btn-sm px-3">
                        Kelola Pengaduan Kerusakan
                    </a>
                    <a href="{{ route('calendar.public') }}" class="btn btn-outline-primary btn-sm px-3 bg-white">
                        Lihat Kalender
                    </a>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Status Perbaikan Fasilitas</h5>
                <a href="{{ route('pic.complaints.index') }}" class="text-primary small fw-bold text-decoration-none">
                    Update Status →
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-sm-4">
                    <a href="{{ route('pic.complaints.index') }}" class="rapi-asset-card pastel-rose">
                        <div>
                            <div class="rapi-asset-value">{{ $stats['belum_diperbaiki'] }}</div>
                            <div class="rapi-asset-label">Belum Diperbaiki</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 pt-2">
                            <span class="rapi-asset-icon"><i class="bi bi-exclamation-circle text-danger"></i></span>
                            <span class="small fw-bold text-danger">Tangani →</span>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-sm-4">
                    <a href="{{ route('pic.complaints.index') }}" class="rapi-asset-card pastel-yellow">
                        <div>
                            <div class="rapi-asset-value">{{ $stats['menunggu_sparepart'] }}</div>
                            <div class="rapi-asset-label">Menunggu Spare Part</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 pt-2">
                            <span class="rapi-asset-icon"><i class="bi bi-hourglass-split text-warning"></i></span>
                            <span class="small fw-bold text-warning">Proses →</span>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-sm-4">
                    <a href="{{ route('pic.complaints.index') }}" class="rapi-asset-card pastel-green">
                        <div>
                            <div class="rapi-asset-value">{{ $stats['sudah_diperbaiki'] }}</div>
                            <div class="rapi-asset-label">Sudah Diperbaiki</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 pt-2">
                            <span class="rapi-asset-icon"><i class="bi bi-check2-circle text-success"></i></span>
                            <span class="small fw-bold text-success">Selesai</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Ruangan Tanggung Jawab --}}
    <div class="card mb-4">
        <div class="card-header">Ruangan Tanggung Jawab Saya</div>
        <div class="card-body">
            <div class="row g-3">
                @forelse($myRooms as $room)
                    <div class="col-md-6 col-xl-4">
                        <div class="p-3 rounded-4 border bg-light bg-opacity-50 h-100">
                            <h6 class="fw-bold mb-1">{{ $room->name }}</h6>
                            <p class="text-muted small mb-2">{{ $room->location ?: 'Lokasi tidak tersedia' }}</p>
                            <div class="d-flex justify-content-between small pt-2 border-top">
                                <span>Total laporan: <strong>{{ $room->complaints_count }}</strong></span>
                                <span class="text-danger">Belum ditangani: <strong>{{ $room->open_complaints_count }}</strong></span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">Belum ada ruangan yang ditugaskan.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Pengaduan Terbaru --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Pengaduan Terbaru</span>
            <a href="{{ route('pic.complaints.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="card-body py-2">
            @forelse($recent_complaints as $complaint)
                @php
                    $badge = match($complaint->status_repair) {
                        'Sudah Diperbaiki' => 'success',
                        'Menunggu Spare Part' => 'warning text-dark',
                        default => 'danger'
                    };
                @endphp
                <div class="rapi-list-row">
                    <div class="d-flex align-items-center gap-3">
                        @if($complaint->photo_path)
                            <img src="{{ Storage::url($complaint->photo_path) }}"
                                 alt="Foto bukti kerusakan"
                                 class="rounded-3 flex-shrink-0"
                                 style="width:56px;height:56px;object-fit:cover;"
                                 onerror="this.style.display='none'">
                        @else
                            <div class="rounded-3 flex-shrink-0 bg-light text-muted d-flex align-items-center justify-content-center small text-center px-1"
                                 style="width:56px;height:56px;font-size:0.68rem;" title="Foto tidak tersedia">
                                Foto tidak tersedia
                            </div>
                        @endif
                        <div>
                            <div class="fw-bold text-dark">{{ $complaint->room->name }}</div>
                            <p class="mb-1 text-muted small">{{ Str::limit($complaint->description, 80) }}</p>
                            <small class="text-muted">Dilaporkan oleh: {{ $complaint->reporter->name }} · {{ $complaint->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    <span class="badge bg-{{ $badge }}">{{ $complaint->status_repair }}</span>
                </div>
            @empty
                <div class="text-center text-muted py-4">Belum ada pengaduan</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

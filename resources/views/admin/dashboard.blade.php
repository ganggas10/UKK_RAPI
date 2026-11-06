@extends('layouts.app')

@section('title', 'Overview Dashboard')

@section('content')
<div class="container-fluid">
    {{-- BARIS ATAS: IKHTISAR UTAMA (KIRI) & KARTU PASTEL ASET (KANAN) PERSIS REFERENSI --}}
    <div class="row g-4 mb-4">
        {{-- Kolom Kiri: Ikhtisar Sistem (Gaya Portfolio Balance) --}}
        <div class="col-12 col-xl-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Ikhtisar Sarpras</h5>
                <span class="text-muted small">Selamat datang, {{ auth()->user()->name }}</span>
            </div>

            <div class="rapi-balance-card">
                <div>
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="rapi-balance-value">{{ $stats['total_rooms'] }} Ruangan</div>
                            <div class="text-muted small fw-semibold mt-1">Total ruang kelas, laboratorium &amp; aula terdaftar</div>
                        </div>
                        <span class="rapi-balance-pill">
                            {{ $stats['total_users'] }} Pengguna
                        </span>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-4 mt-2 border-top border-primary border-opacity-10">
                    <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm px-3">
                        + Tambah Agenda
                    </a>
                    <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-primary btn-sm px-3 bg-white">
                        Kelola Ruangan
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary btn-sm px-3 bg-white">
                        Laporan
                    </a>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: 3 Kartu Pastel (Gaya Your Assets) --}}
        <div class="col-12 col-xl-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Status Operasional</h5>
                <a href="{{ route('admin.pic-assignments.index') }}" class="text-primary small fw-bold text-decoration-none">
                    Manajemen PIC →
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-sm-4">
                    <a href="{{ route('admin.events.index') }}" class="rapi-asset-card pastel-purple">
                        <div>
                            <div class="rapi-asset-value">{{ $stats['pending_events'] }} Agenda</div>
                            <div class="rapi-asset-label">Menunggu Persetujuan</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 pt-2">
                            <span class="rapi-asset-icon"><i class="bi bi-calendar2-week text-primary"></i></span>
                            <span class="small fw-bold text-primary">Tinjau →</span>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-sm-4">
                    <a href="{{ route('admin.users.index') }}" class="rapi-asset-card pastel-green">
                        <div>
                            <div class="rapi-asset-value">{{ $stats['total_users'] }} Akun</div>
                            <div class="rapi-asset-label">Pengguna Terdaftar</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 pt-2">
                            <span class="rapi-asset-icon"><i class="bi bi-people text-success"></i></span>
                            <span class="small fw-bold text-success">Kelola →</span>
                        </div>
                    </a>
                </div>

                <div class="col-12 col-sm-4">
                    <a href="{{ route('admin.complaints.index') }}" class="rapi-asset-card pastel-yellow">
                        <div>
                            <div class="rapi-asset-value">{{ $stats['open_complaints'] }} Laporan</div>
                            <div class="rapi-asset-label">Kerusakan Terbuka</div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mt-4 pt-2">
                            <span class="rapi-asset-icon"><i class="bi bi-tools text-warning"></i></span>
                            <span class="small fw-bold text-warning">Pantau →</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- BARIS BAWAH: DAFTAR AGENDA & PENGADUAN (KIRI) + KARTU GELAP KALENDER (KANAN) --}}
    <div class="row g-4">
        <div class="col-12 col-xl-8">
            {{-- Daftar Agenda Terbaru --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Agenda Terbaru</span>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body py-2">
                    @forelse($recent_events as $event)
                        @php
                            $badge = match($event->status) {
                                'approved' => 'success',
                                'pending' => 'warning text-dark',
                                'rejected' => 'danger',
                                'moved' => 'info',
                                default => 'secondary'
                            };
                        @endphp
                        <div class="rapi-list-row">
                            <div class="d-flex align-items-center gap-3">
                                <span class="rapi-row-icon">
                                    <i class="bi bi-calendar-event"></i>
                                </span>
                                <div>
                                    <div class="fw-bold text-dark">{{ $event->title }}</div>
                                    <small class="text-muted">{{ $event->room->name }} · {{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }}</small>
                                </div>
                            </div>
                            <span class="badge bg-{{ $badge }}">{{ ucfirst($event->status) }}</span>
                        </div>
                    @empty
                        <div class="text-muted text-center py-4">Tidak ada agenda terbaru.</div>
                    @endforelse
                </div>
            </div>

            {{-- Kerusakan Belum Ditangani --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Kerusakan Belum Ditangani</span>
                    <a href="{{ route('admin.complaints.index') }}" class="btn btn-sm btn-outline-primary">Semua Pengaduan</a>
                </div>
                <div class="card-body py-2">
                    <a href="{{ route('admin.complaints.whatsapp', 0) }}" class="d-none">dummy</a>
                    @forelse($open_complaints as $complaint)
                        <div class="rapi-list-row">
                            <div class="d-flex align-items-center gap-3">
                                @if($complaint->photo_path)
                                    <img src="{{ Storage::url($complaint->photo_path) }}"
                                         alt="Foto bukti kerusakan"
                                         class="rounded-3 flex-shrink-0"
                                         style="width:48px;height:48px;object-fit:cover;"
                                         onerror="this.style.display='none'">
                                @else
                                    <span class="rapi-row-icon bg-danger">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </span>
                                @endif
                                <div>
                                    <div class="fw-bold text-dark">{{ $complaint->room->name }}</div>
                                    <small class="text-muted d-block">{{ Str::limit($complaint->description, 65) }}</small>
                                    <small class="text-danger fw-semibold">Pelapor: {{ $complaint->reporter->name }}</small>
                                </div>
                            </div>
                            <div>
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
                        <div class="text-success text-center py-4 fw-semibold">Semua kerusakan sudah ditangani!</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Kolom Kanan Bawah: Kartu Gelap (Persis Referensi "Earn free crypto") --}}
        <div class="col-12 col-xl-4">
            <div class="rapi-dark-card">
                <div>
                    <span class="badge bg-primary mb-3">Kalender &amp; Arsip</span>
                    <h4>
                        Kelola <span class="pill-highlight">Kalender</span> &amp; Hari Penting Sekolah!
                    </h4>
                    <p class="text-white-50 small mt-2 mb-4">
                        Sinkronisasikan jadwal libur nasional Indonesia, pantau riwayat agenda ruangan, dan unduh laporan bulanan sarpras dengan cepat.
                    </p>
                </div>

                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('admin.national-holidays.index') }}" class="btn-light-blue">
                        Kelola Hari Penting →
                    </a>
                    <a href="{{ route('waka.agenda.history') }}" class="text-white-50 small text-decoration-none mt-2">
                        Lihat Riwayat Seluruh Agenda →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

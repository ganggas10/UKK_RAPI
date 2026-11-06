@extends('layouts.app')
@section('title', 'Dashboard Guru')
@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-1">Dashboard Guru / Organisasi</h2>
    <p class="text-muted mb-4">Selamat datang, {{ auth()->user()->name }}</p>

    {{-- ============================================================
         NOTIFIKASI PEMINDAHAN RUANG (Pop-up / Alert)
         Logika bisnis #5: Guru menerima notifikasi otomatis
         ============================================================ --}}
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

    {{-- Quick Actions --}}
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('guru.events.create') }}" class="btn btn-primary quick-action">Ajukan Pinjam Ruang</a>
        <a href="{{ route('guru.complaints.create') }}" class="btn btn-danger quick-action">Lapor Kerusakan</a>
    </div>

    {{-- Peminjaman Terbaru --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light fw-semibold d-flex justify-content-between">
                    Peminjaman Terbaru Saya
            <a href="{{ route('guru.events.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="list-group list-group-flush">
            @forelse($myEvents as $event)
            <div class="list-group-item">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>{{ $event->title }}</strong>
                        <br><small class="text-muted">{{ $event->room->name }} | {{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }} | {{ substr($event->start_time,0,5) }}–{{ substr($event->end_time,0,5) }}</small>
                    </div>
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
                    <span class="badge bg-{{ $badge }} align-self-center">{{ $label }}</span>
                </div>
            </div>
            @empty
            <div class="list-group-item text-center text-muted py-4">Belum ada peminjaman.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .quick-action {
        min-width: 190px;
        padding: 0.55rem 1rem;
    }
</style>
@endpush
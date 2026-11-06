@extends('layouts.app')

@section('title', 'Detail Riwayat Agenda')

@section('content')
@php
    $statusLabels = ['pending' => 'Menunggu Persetujuan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'moved' => 'Dipindahkan'];
    $statusBadges = ['pending' => 'warning', 'approved' => 'primary', 'rejected' => 'danger', 'moved' => 'info'];
@endphp
<div class="container-fluid py-4 px-3 px-lg-4">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4"><div><p class="text-primary fw-semibold mb-1">ARSIP AGENDA</p><h2 class="fw-bold mb-0">Detail Riwayat Agenda</h2></div><a href="{{ route('waka.agenda.history') }}" class="btn btn-outline-secondary">Kembali</a></div>
    <div class="card shadow-sm mb-4"><div class="card-body p-4"><div class="d-flex flex-wrap justify-content-between gap-3"><div><span class="text-muted">Kode Agenda</span><h4 class="fw-bold">#{{ $event->id }} · {{ $event->title }}</h4></div><span class="badge text-bg-{{ $statusBadges[$event->status] ?? 'secondary' }} align-self-start">{{ $statusLabels[$event->status] ?? ucfirst($event->status) }}</span></div></div></div>
    <div class="row g-4">
        <div class="col-lg-7"><div class="card shadow-sm h-100"><div class="card-header bg-white fw-bold">Informasi Agenda</div><div class="card-body"><dl class="row mb-0"><dt class="col-sm-4">Deskripsi</dt><dd class="col-sm-8">{{ $event->description ?: 'Tidak tersedia' }}</dd><dt class="col-sm-4">Tanggal Mulai</dt><dd class="col-sm-8">{{ $event->bookingStartDate()->format('d/m/Y') }}</dd><dt class="col-sm-4">Tanggal Selesai</dt><dd class="col-sm-8">{{ $event->bookingEndDate()->format('d/m/Y') }}</dd><dt class="col-sm-4">Durasi</dt><dd class="col-sm-8">{{ $event->bookingDurationInDays() }} hari</dd><dt class="col-sm-4">Waktu</dt><dd class="col-sm-8">{{ substr($event->start_time, 0, 5) }}–{{ substr($event->end_time, 0, 5) }}</dd><dt class="col-sm-4">Ruangan</dt><dd class="col-sm-8">{{ $event->room?->name ?: 'Tidak tersedia' }}</dd><dt class="col-sm-4">Kapasitas</dt><dd class="col-sm-8">{{ $event->room?->capacity ? $event->room->capacity . ' orang' : 'Tidak tersedia' }}</dd><dt class="col-sm-4">Diajukan</dt><dd class="col-sm-8">{{ $event->created_at?->format('d/m/Y H:i') ?: 'Tidak tersedia' }}</dd></dl></div></div></div>
        <div class="col-lg-5"><div class="card shadow-sm h-100"><div class="card-header bg-white fw-bold">Informasi Pemohon</div><div class="card-body"><dl class="row mb-0"><dt class="col-sm-5">Nama</dt><dd class="col-sm-7">{{ $event->user?->name ?: 'Tidak tersedia' }}</dd><dt class="col-sm-5">Role</dt><dd class="col-sm-7"><span class="badge" style="background-color: {{ $event->creatorColor() }}; color: #ffffff;">{{ $event->creatorRoleLabel() }}</span></dd><dt class="col-sm-5">Email</dt><dd class="col-sm-7 text-break">{{ $event->user?->email ?: 'Tidak tersedia' }}</dd><dt class="col-sm-5">Nomor HP</dt><dd class="col-sm-7">{{ $event->user?->phone ?: 'Tidak tersedia' }}</dd></dl></div></div></div>
    </div>
    @if($event->status === 'moved')<div class="card shadow-sm mt-4"><div class="card-header bg-white fw-bold">Informasi Pemindahan</div><div class="card-body"><p class="mb-1"><strong>Ruangan baru:</strong> {{ $event->movedToRoom?->name ?: 'Tidak tersedia' }}</p><p class="mb-1"><strong>Alasan:</strong> {{ $event->move_reason ?: 'Tidak tersedia' }}</p><p class="mb-0"><strong>Dipindahkan oleh:</strong> {{ $event->movedByUser?->name ?: 'Tidak tersedia' }}</p></div></div>@endif
    <div class="alert alert-info mt-4 mb-0">Halaman ini bersifat read-only. Sistem saat ini belum menyimpan tanggal persetujuan, alasan penolakan, pembatalan, atau status pelaksanaan terpisah.</div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Detail Laporan Kerusakan')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('guru.complaints.index') }}" class="btn btn-outline-secondary btn-sm">
            Kembali
        </a>
        <h2 class="fw-bold mb-0">Detail Laporan Kerusakan</h2>
    </div>

    @php
        $statusMap = [
            'Belum Diperbaiki'    => ['class' => 'bg-danger'],
            'Menunggu Spare Part' => ['class' => 'bg-warning text-dark'],
            'Sudah Diperbaiki'    => ['class' => 'bg-success'],
        ];
        $statusInfo = $statusMap[$complaint->status_repair] ?? ['class' => 'bg-secondary'];
    @endphp

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
            <div class="text-white">
                <strong>Laporan #{{ $complaint->id }}</strong>
            </div>
            <span class="badge {{ $statusInfo['class'] }} fs-6">
                {{ $complaint->status_repair }}
            </span>
        </div>

        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-muted">Ruangan</dt>
                <dd class="col-sm-8">
                    <strong>{{ $complaint->room->name }}</strong>
                    @if($complaint->room->location)
                        <small class="text-muted">({{ $complaint->room->location }})</small>
                    @endif
                </dd>

                <dt class="col-sm-4 text-muted">Pelapor</dt>
                <dd class="col-sm-8">{{ $complaint->reporter->name }}</dd>

                <dt class="col-sm-4 text-muted">Tanggal Lapor</dt>
                <dd class="col-sm-8">{{ $complaint->created_at->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-4 text-muted">Deskripsi Kerusakan</dt>
                <dd class="col-sm-8">
                    <div class="bg-light p-3 rounded border">
                        {{ $complaint->description }}
                    </div>
                </dd>

                <dt class="col-sm-4 text-muted">Status Perbaikan</dt>
                <dd class="col-sm-8">
                    <span class="badge {{ $statusInfo['class'] }} fs-6">
                        {{ $complaint->status_repair }}
                    </span>
                </dd>

                @if($complaint->pic_notes)
                <dt class="col-sm-4 text-muted">Catatan PIC</dt>
                <dd class="col-sm-8">
                    <div class="alert alert-info py-2 mb-0">
                        {{ $complaint->pic_notes }}
                    </div>
                </dd>
                @endif
            </dl>
        </div>

        {{-- Foto Bukti Kerusakan --}}
        @if($complaint->photo_path)
        <div class="card-footer bg-light">
            <p class="text-muted small mb-2">Foto Bukti Kerusakan</p>
            <img src="{{ Storage::url($complaint->photo_path) }}"
                 alt="Foto Kerusakan"
                 class="img-fluid rounded shadow-sm"
                 style="max-height: 400px; object-fit: contain; width: 100%; background: #f8f9fa;"
                 onerror="this.style.display='none'">
        </div>
        @endif
    </div>

    {{-- Timeline Status --}}
    <div class="card shadow-sm mt-3">
        <div class="card-header bg-light fw-semibold">
            Alur Status Penanganan
        </div>
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center position-relative">
                <div style="position:absolute;top:50%;left:0;right:0;height:3px;background:#dee2e6;z-index:0"></div>

                @php
                    $steps = ['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'];
                    $currentIdx = array_search($complaint->status_repair, $steps);
                @endphp

                @foreach($steps as $i => $step)
                <div class="text-center" style="z-index:1; flex:1">
                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center fw-bold"
                         style="width:36px;height:36px;border:3px solid {{ $i <= $currentIdx ? '#198754' : '#dee2e6' }};background:{{ $i <= $currentIdx ? '#198754' : '#fff' }};color:{{ $i <= $currentIdx ? '#fff' : '#adb5bd' }}">
                        {{ $i + 1 }}
                    </div>
                    <small class="d-block mt-1" style="font-size:0.7rem;color:{{ $i <= $currentIdx ? '#198754' : '#adb5bd' }}">
                        {{ $step === 'Belum Diperbaiki' ? 'Belum' : ($step === 'Menunggu Spare Part' ? 'Menunggu' : 'Selesai') }}
                    </small>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('guru.complaints.index') }}" class="btn btn-outline-primary">
            Semua Laporan
        </a>
        <a href="{{ route('guru.complaints.create') }}" class="btn btn-danger ms-2">
            Laporan Baru
        </a>
    </div>
</div>
@endsection

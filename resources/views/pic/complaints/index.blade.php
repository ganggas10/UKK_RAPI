@extends('layouts.app')

@section('title', 'Kelola Pengaduan')

@section('content')
<div class="container-fluid py-4">
    <h2 class="fw-bold mb-4">Kelola Pengaduan Kerusakan</h2>

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

    <div class="row g-4">
        @forelse($complaints as $complaint)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card shadow-sm h-100 border-start border-4
                @if($complaint->status_repair === 'Belum Diperbaiki') border-danger
                @elseif($complaint->status_repair === 'Menunggu Spare Part') border-warning
                @else border-success @endif">
                <div class="card-body">
                    {{-- Foto bukti --}}
                    @if($complaint->photo_path)
                    <img src="{{ Storage::url($complaint->photo_path) }}"
                        alt="Foto bukti kerusakan di {{ $complaint->room->name }}"
                        class="img-fluid rounded mb-3" style="max-height: 220px; object-fit:cover; width:100%;">
                    @else
                    <div class="rounded mb-3 d-flex align-items-center justify-content-center bg-light text-muted"
                        style="height: 140px;">
                        <span>Foto tidak tersedia</span>
                    </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold mb-0">{{ $complaint->room->name }}</h6>
                        @php
                            $badge = match($complaint->status_repair) {
                                'Sudah Diperbaiki' => 'success',
                                'Menunggu Spare Part' => 'warning text-dark',
                                default => 'danger'
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ $complaint->status_repair }}</span>
                    </div>

                    <p class="text-muted small mb-2">{{ $complaint->description }}</p>
                    <small class="text-muted">Lapor: {{ $complaint->reporter->name }} | {{ $complaint->created_at->format('d/m/Y') }}</small>

                    {{-- Form update status / Status Final --}}
                    @if($complaint->status_repair === 'Sudah Diperbaiki')
                        <div class="mt-3 pt-3 border-top">
                            @if($complaint->pic_notes)
                                <div class="bg-light rounded p-2 mb-2 small text-secondary">
                                    <strong class="d-block text-dark mb-1">Catatan Perbaikan PIC:</strong>
                                    {{ $complaint->pic_notes }}
                                </div>
                            @endif
                            <div class="d-flex align-items-center justify-content-center gap-2 py-2 px-3 rounded bg-success bg-opacity-10 text-success fw-semibold small">
                                <i class="bi bi-lock-fill"></i>
                                <span>Sudah Diperbaiki (Status Final)</span>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('pic.complaints.status', $complaint) }}" method="POST" class="mt-3">
                            @csrf @method('PATCH')
                            <select name="status_repair" class="form-select form-select-sm mb-2">
                                @foreach(['Belum Diperbaiki', 'Menunggu Spare Part', 'Sudah Diperbaiki'] as $status)
                                    <option value="{{ $status }}"
                                        {{ $complaint->status_repair === $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                            <textarea name="pic_notes" class="form-control form-control-sm mb-2" rows="2"
                                      placeholder="Catatan/laporan kondisi...">{{ $complaint->pic_notes }}</textarea>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                💾 Perbarui Status
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">Tidak ada pengaduan untuk ruangan Anda saat ini.</div>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $complaints->links() }}</div>
</div>
@endsection
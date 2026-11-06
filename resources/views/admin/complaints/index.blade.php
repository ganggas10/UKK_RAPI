@extends('layouts.app')

@section('title', 'Pengaduan Ruangan')

@section('content')
<div class="container-fluid py-4 px-3 px-lg-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">MONITORING WAKA / ADMIN</p>
            <h2 class="fw-bold mb-1">Pengaduan Ruangan</h2>
            <p class="text-muted mb-0">Pantau laporan kerusakan dan hubungi PIC ruangan yang bertanggung jawab.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-light border fs-6">{{ number_format($complaints->total()) }} laporan</span>
            <a href="{{ route('admin.complaints.create') }}" class="btn btn-danger">Lapor Kerusakan</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No.</th>
                        <th>Ruangan</th>
                        <th>Pelapor</th>
                        <th>Kerusakan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>PIC Ruangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($complaints as $complaint)
                    @php
                        $badge = match($complaint->status_repair) {
                            'Sudah Diperbaiki' => 'success',
                            'Menunggu Spare Part' => 'warning text-dark',
                            default => 'danger',
                        };
                    @endphp
                    <tr>
                        <td>{{ $complaints->firstItem() + $loop->index }}</td>
                        <td><strong>{{ $complaint->room?->name ?? 'Tidak tersedia' }}</strong><br><small class="text-muted">{{ $complaint->room?->location }}</small></td>
                        <td>{{ $complaint->reporter?->name ?? 'Tidak tersedia' }}<br><small class="text-muted">{{ $complaint->reporter?->role ? ucfirst($complaint->reporter->role) : '' }}</small></td>
                        <td class="complaint-description">{{ $complaint->description }}</td>
                        <td><span class="badge bg-{{ $badge }}">{{ $complaint->status_repair }}</span></td>
                        <td>{{ $complaint->created_at?->format('d/m/Y H:i') ?? 'Tidak tersedia' }}</td>
                        <td>{{ $complaint->room?->pic?->name ?? 'Belum ditugaskan' }}<br><small class="text-muted">{{ $complaint->room?->pic?->phone ?? 'Nomor belum tersedia' }}</small></td>
                        <td>
                            @if($complaint->room?->pic?->phone)
                                <a href="{{ route('admin.complaints.whatsapp', $complaint) }}" target="_blank" class="btn btn-sm btn-success" title="Hubungi PIC melalui WhatsApp">
                                    WA PIC
                                </a>
                            @else
                                <span class="badge bg-secondary">WA tidak tersedia</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-5"><p class="text-muted mb-0 mt-2">Belum ada pengaduan ruangan.</p></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($complaints->hasPages())
            <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-3">
                <small class="text-muted">
                    Menampilkan {{ $complaints->firstItem() }}–{{ $complaints->lastItem() }} dari {{ $complaints->total() }} laporan
                </small>
                <div class="complaints-pagination">{{ $complaints->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .complaint-description { min-width: 240px; max-width: 360px; }
    .complaints-pagination .pagination { margin: 0; gap: 0.35rem; }
    .complaints-pagination .page-item .page-link {
        min-width: 38px;
        padding: 0.45rem 0.7rem;
        color: #1e40af;
        background: #fff;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        text-align: center;
        font-weight: 700;
        transition: color 0.2s ease, background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    }
    .complaints-pagination .page-item:not(.disabled) .page-link:hover {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.2);
        transform: translateY(-1px);
    }
    .complaints-pagination .page-item.active .page-link {
        color: #fff;
        background: linear-gradient(135deg, #1e40af, #2563eb);
        border-color: #2563eb;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.22);
    }
    .complaints-pagination .page-item.disabled .page-link {
        color: #94a3b8;
        background: #f8fafc;
        border-color: #e2e8f0;
        cursor: not-allowed;
    }
    @media (max-width: 576px) {
        .card-footer { align-items: flex-start !important; }
        .complaints-pagination { width: 100%; overflow-x: auto; padding-bottom: 0.15rem; }
        .complaints-pagination .pagination { width: max-content; }
    }
</style>
@endpush
@extends('layouts.app')

@section('title', 'Riwayat Pemindahan Ruangan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Riwayat Pemindahan Ruangan</h2>
            <p class="text-muted small mb-0">Catatan pemindahan ruangan kegiatan sekolah beserta status pengiriman notifikasi WhatsApp.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Manajemen Agenda
            </a>
        </div>
    </div>

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
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            {{ session('info') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Riwayat Pemindahan --}}
    <div class="card shadow-sm border mb-4 rounded-3 bg-white">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('admin.events.move-history') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6 col-lg-4">
                        <label for="search" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-search me-1"></i>Pencarian
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="search" id="search" name="search" class="form-control border-start-0"
                                   placeholder="Nama kegiatan, pemohon, atau ruangan..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-6 col-md-3 col-lg-3">
                        <label for="wa_status" class="form-label fw-semibold small text-muted mb-1">
                            <i class="bi bi-whatsapp me-1"></i>Status WhatsApp
                        </label>
                        <select id="wa_status" name="wa_status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="belum_dikirim" @selected(request('wa_status') === 'belum_dikirim')>Belum Dikirim</option>
                            <option value="berhasil_dikirim" @selected(request('wa_status') === 'berhasil_dikirim')>Berhasil Dikirim</option>
                            <option value="gagal_dikirim" @selected(request('wa_status') === 'gagal_dikirim')>Gagal Dikirim</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3 col-lg-auto d-flex gap-2 ms-lg-auto">
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-funnel-fill"></i> Terapkan
                        </button>
                        <a href="{{ route('admin.events.move-history') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Riwayat --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <span class="text-muted small">Total <strong>{{ $moveHistories->total() }}</strong> riwayat pemindahan ruangan</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama Kegiatan</th>
                        <th>Peminjam</th>
                        <th>Ruangan Asal & Pengganti</th>
                        <th>Alasan Pemindahan</th>
                        <th>Waktu Pemindahan</th>
                        <th>Status WhatsApp</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($moveHistories as $index => $history)
                    <tr>
                        <td>{{ $moveHistories->firstItem() + $index }}</td>
                        <td>
                            <strong>{{ $history->event?->title ?? '-' }}</strong>
                            <div class="text-muted small">ID Agenda: #{{ $history->event_id }}</div>
                        </td>
                        <td>
                            <div>{{ $history->user?->name ?? 'Pengguna' }}</div>
                            <span class="badge bg-secondary" style="font-size: 0.72rem;">{{ ucfirst($history->user?->role ?? 'User') }}</span>
                            <div class="text-muted small">{{ $history->formattedPhone() ? '+'.$history->formattedPhone() : 'No HP kosong' }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $history->old_room_name }}</span>
                                <i class="bi bi-arrow-right text-muted"></i>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $history->new_room_name }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="small" title="{{ $history->move_reason }}">{{ Str::limit($history->move_reason, 45) }}</span>
                        </td>
                        <td>
                            <div class="small fw-semibold">{{ $history->created_at->format('d/m/Y H:i') }}</div>
                            <small class="text-muted">Oleh: {{ $history->admin?->name ?? 'Admin' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $history->statusBadgeClass() }}">
                                {{ $history->statusLabel() }}
                            </span>
                            @if($history->wa_sent_at)
                                <div class="text-muted small">{{ $history->wa_sent_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $history->id }}" title="Lihat Detail Pesan">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if($history->hasValidPhone())
                                    <a href="{{ route('admin.events.move.whatsapp', $history) }}" target="_blank"
                                       class="btn btn-sm btn-success d-inline-flex align-items-center gap-1"
                                       title="{{ $history->wa_status === 'berhasil_dikirim' ? 'Kirim Ulang WhatsApp' : 'Kirim WhatsApp' }}">
                                        <i class="bi bi-whatsapp"></i>
                                        <span class="d-none d-md-inline">{{ $history->wa_status === 'berhasil_dikirim' ? 'Kirim Ulang' : 'Kirim WA' }}</span>
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-secondary opacity-50" disabled title="Nomor HP belum tersedia di profil pemohon">
                                        <i class="bi bi-whatsapp"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Modal Detail Riwayat Pemindahan --}}
                    <div class="modal fade" id="detailModal{{ $history->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Detail Pemindahan: {{ $history->event?->title }}</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <ul class="list-group list-group-flush mb-3 small">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Peminjam:</span>
                                            <strong>{{ $history->user?->name }} ({{ ucfirst($history->user?->role) }})</strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">No. WhatsApp:</span>
                                            <strong class="{{ $history->hasValidPhone() ? 'text-success' : 'text-danger' }}">
                                                {{ $history->formattedPhone() ? '+'.$history->formattedPhone() : ($history->target_phone ?: 'Belum diisi') }}
                                            </strong>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Ruangan Asal:</span>
                                            <span class="text-danger fw-bold">{{ $history->old_room_name }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Ruangan Pengganti:</span>
                                            <span class="text-success fw-bold">{{ $history->new_room_name }}</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span class="text-muted">Status Pengiriman:</span>
                                            <span class="badge bg-{{ $history->statusBadgeClass() }}">{{ $history->statusLabel() }}</span>
                                        </li>
                                        <li class="list-group-item">
                                            <span class="text-muted d-block mb-1">Alasan Pemindahan:</span>
                                            <div class="p-2 bg-light rounded">{{ $history->move_reason }}</div>
                                        </li>
                                    </ul>

                                    <label class="form-label fw-semibold small text-muted">Isi Pesan WhatsApp:</label>
                                    <div class="p-3 bg-light rounded border small" style="white-space: pre-wrap; max-height: 180px; overflow-y: auto;">{{ $history->wa_message }}</div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    @if($history->hasValidPhone())
                                        <a href="{{ route('admin.events.move.whatsapp', $history) }}" target="_blank" class="btn btn-success">
                                            <i class="bi bi-whatsapp me-1"></i> {{ $history->wa_status === 'berhasil_dikirim' ? 'Kirim Ulang' : 'Kirim WhatsApp' }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            Belum ada riwayat pemindahan ruangan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $moveHistories->links() }}</div>
</div>
@endsection

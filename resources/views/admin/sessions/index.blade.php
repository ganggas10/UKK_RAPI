@extends('layouts.app')

@section('title', 'Kelola Sesi - Admin')

@section('content')
<div class="container-fluid py-4">
    {{-- Header & Tombol Tambah --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i>Kelola Sesi Jam Operasional
            </h2>
            <p class="text-muted small mb-0">
                Atur pembagian jam dan sesi penggunaan ruangan di sekolah. Sesi aktif akan muncul pada formulir peminjaman ruangan.
            </p>
        </div>
        <button type="button" class="btn btn-primary px-3 shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#createSessionModal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Sesi
        </button>
    </div>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
            <i class="bi bi-exclamation-octagon-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Terdapat kesalahan input:</div>
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Ringkasan Kartu Metrik --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="rapi-asset-card" style="background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(255,255,255,0.9)); border: 1px solid rgba(37,99,235,0.18);">
                <div>
                    <div class="rapi-asset-value text-primary">{{ $stats['total'] }}</div>
                    <div class="rapi-asset-label">Total Sesi Terdaftar</div>
                </div>
                <div class="mt-3 text-muted small"><i class="bi bi-layers me-1"></i>Seluruh sesi sistem</div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="rapi-asset-card" style="background: linear-gradient(135deg, rgba(22,163,74,0.08), rgba(255,255,255,0.9)); border: 1px solid rgba(22,163,74,0.18);">
                <div>
                    <div class="rapi-asset-value text-success">{{ $stats['active'] }}</div>
                    <div class="rapi-asset-label">Sesi Aktif</div>
                </div>
                <div class="mt-3 text-success small"><i class="bi bi-check-circle me-1"></i>Bisa dipilih saat pinjam</div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="rapi-asset-card" style="background: linear-gradient(135deg, rgba(100,116,139,0.08), rgba(255,255,255,0.9)); border: 1px solid rgba(100,116,139,0.18);">
                <div>
                    <div class="rapi-asset-value text-secondary">{{ $stats['inactive'] }}</div>
                    <div class="rapi-asset-label">Sesi Nonaktif</div>
                </div>
                <div class="mt-3 text-secondary small"><i class="bi bi-dash-circle me-1"></i>Disembunyikan dari form</div>
            </div>
        </div>
    </div>

    {{-- Tabel Sesi Jam --}}
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-calendar3-range me-2 text-primary"></i>Daftar Sesi Jam Sekolah</span>
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill small">
                {{ $sessions->count() }} Sesi Terdaftar
            </span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small">
                    <tr>
                        <th style="width: 70px;" class="text-center">Urutan</th>
                        <th>Nama Sesi</th>
                        <th>Jam Mulai</th>
                        <th>Jam Selesai</th>
                        <th>Rentang Waktu</th>
                        <th class="text-center">Status</th>
                        <th class="text-end" style="width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $session)
                    <tr>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $session->sort_order }}</span>
                        </td>
                        <td>
                            <strong class="text-dark">{{ $session->name }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border border-primary border-opacity-25 px-2 py-1">
                                <i class="bi bi-clock me-1"></i>{{ $session->formatted_start_time }} WIB
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border border-primary border-opacity-25 px-2 py-1">
                                <i class="bi bi-clock-history me-1"></i>{{ $session->formatted_end_time }} WIB
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold text-secondary small">{{ $session->time_range }} WIB</span>
                        </td>
                        <td class="text-center">
                            @if($session->is_active)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1">
                                    <i class="bi bi-dash-circle-fill me-1"></i>Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                {{-- Tombol Toggle Aktif/Nonaktif --}}
                                <form action="{{ route('admin.sessions.toggle', $session) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $session->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $session->is_active ? 'Nonaktifkan Sesi' : 'Aktifkan Sesi' }}">
                                        <i class="bi {{ $session->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                        <span class="d-none d-md-inline ms-1">{{ $session->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                                    </button>
                                </form>

                                {{-- Tombol Edit Modal --}}
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#editSessionModal{{ $session->id }}" title="Edit Sesi">
                                    <i class="bi bi-pencil-square"></i>
                                    <span class="d-none d-md-inline ms-1">Edit</span>
                                </button>

                                {{-- Tombol Hapus Modal --}}
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        data-bs-toggle="modal" data-bs-target="#deleteSessionModal{{ $session->id }}" title="Hapus Sesi">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            {{-- MODAL EDIT SESI --}}
                            <div class="modal fade text-start" id="editSessionModal{{ $session->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <form action="{{ route('admin.sessions.update', $session) }}" method="POST">
                                            @csrf @method('PUT')
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-pencil-square me-2"></i>Edit Pengaturan Sesi
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nama Sesi <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control" value="{{ old('name', $session->name) }}" required>
                                                    <small class="text-muted">Contoh: Sesi 1, Sesi Pagi, Sesi 2...</small>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                                                        <input type="time" name="start_time" class="form-control"
                                                               value="{{ old('start_time', $session->formatted_start_time) }}" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                                                        <input type="time" name="end_time" class="form-control"
                                                               value="{{ old('end_time', $session->formatted_end_time) }}" required>
                                                    </div>
                                                </div>

                                                <div class="row g-3 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label fw-semibold">Nomor Urut</label>
                                                        <input type="number" name="sort_order" class="form-control"
                                                               value="{{ old('sort_order', $session->sort_order) }}" min="0">
                                                    </div>
                                                    <div class="col-6 d-flex align-items-end">
                                                        <div class="form-check form-switch mb-2">
                                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_active_{{ $session->id }}"
                                                                {{ old('is_active', $session->is_active) ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-semibold" for="edit_active_{{ $session->id }}">Status Aktif</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="alert alert-info py-2 mb-0 small">
                                                    <i class="bi bi-info-circle me-1"></i>Perubahan jam sesi hanya berlaku pada peminjaman baru; riwayat jadwal lama yang sudah disepakati tidak akan berubah.
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary px-3">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- MODAL KONFIRMASI HAPUS --}}
                            <div class="modal fade text-start" id="deleteSessionModal{{ $session->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title fw-bold">
                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Hapus Sesi
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4 text-center">
                                            <div class="mb-3 text-danger display-5">
                                                <i class="bi bi-trash"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">Apakah Anda yakin ingin menghapus {{ $session->name }}?</h6>
                                            <p class="text-muted small mb-0">
                                                Rentang waktu: <strong>{{ $session->time_range }} WIB</strong>.<br>
                                                Sesi hanya dapat dihapus jika tidak terhubung dengan data peminjaman yang masih aktif.
                                            </p>
                                        </div>
                                        <div class="modal-footer bg-light justify-content-center">
                                            <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Batal</button>
                                            <form action="{{ route('admin.sessions.destroy', $session) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger px-4">Ya, Hapus Sesi</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox display-6 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada sesi jam operasional yang dibuat. Silakan tambahkan sesi baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH SESI BARU --}}
<div class="modal fade" id="createSessionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.sessions.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Sesi Jam Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Sesi <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Sesi 12" required>
                        <small class="text-muted">Nama unik untuk sesi jam pelajaran/operasional.</small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', '07:00') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', '08:30') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Nomor Urut</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order') }}" min="0" placeholder="Otomatis">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="create_active" checked>
                                <label class="form-check-label fw-semibold" for="create_active">Status Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">Simpan Sesi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Tambah Agenda - Admin')

@section('content')
<div class="container py-4" style="max-width: 720px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-sm">
            Kembali
        </a>
        <h2 class="fw-bold mb-0">Tambah Agenda / Peminjaman Ruang</h2>
    </div>

    @if(session('conflict_error'))
        <div class="alert alert-danger border-danger shadow-sm">
            {{ session('conflict_error') }}
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <strong>Form Pengajuan Agenda (Admin/Waka — Langsung Disetujui)</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.events.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Judul Kegiatan <span class="text-danger">*</span></label>
                    <input type="text" name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           placeholder="Contoh: Rapat Dinas, Upacara, Seminar...">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="description"
                              class="form-control @error('description') is-invalid @enderror"
                              rows="2"
                              placeholder="Keterangan tambahan (opsional)">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ruangan <span class="text-danger">*</span></label>
                    <select name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach($rooms->groupBy('location') as $category => $categoryRooms)
                            <optgroup label="{{ $category ?: 'Lainnya' }}">
                                @foreach($categoryRooms->sortBy('name') as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }} — Kap. {{ $room->capacity }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Mulai Peminjaman <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        <div class="form-text text-muted small">Wajib minimal H-1 sebelum penggunaan ruangan.</div>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Selesai Peminjaman <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                        <div class="form-text text-muted small">Sama atau setelah tanggal mulai.</div>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div id="h1_warning" class="alert alert-warning py-2 mb-0 d-none">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <strong>Perhatian:</strong> Pengajuan peminjaman ruangan wajib dilakukan minimal 1 hari sebelum tanggal penggunaan. Silakan pilih tanggal penggunaan yang lain.
                        </div>
                        <div id="duration_info" class="alert alert-info py-2 mb-0 d-none"></div>
                    </div>
                </div>

                <div class="row mb-4">
                    @foreach(['start_session' => 'Sesi Mulai', 'end_session' => 'Sesi Selesai'] as $field => $label)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ $label }} <span class="text-danger">*</span></label>
                            <select name="{{ $field }}" class="form-select @error($field) is-invalid @enderror" required>
                                <option value="">-- Pilih Sesi --</option>
                                @foreach($sessions as $session)
                                    <option value="{{ $session->name }}" @selected(old($field) === $session->name)>
                                        {{ $session->name }} ({{ $session->formatted_start_time }} - {{ $session->formatted_end_time }})
                                    </option>
                                @endforeach
                            </select>
                            @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>

                <div class="alert alert-info py-2 mb-3">
                    <small>Agenda yang dibuat Admin/Waka akan <strong>langsung berstatus Disetujui</strong> dan tampil di kalender publik.</small>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        Tambah Agenda
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const start = document.getElementById('start_date');
    const end = document.getElementById('end_date');
    const info = document.getElementById('duration_info');
    const warning = document.getElementById('h1_warning');
    const submitBtn = document.getElementById('submit_btn');

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const updateValidation = function () {
        if (!start.value) return;

        const startDate = new Date(`${start.value}T00:00:00`);
        const diffDays = Math.round((startDate - today) / 86400000);

        if (diffDays < 1) {
            warning.classList.remove('d-none');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50');
            start.classList.add('is-invalid');
        } else {
            warning.classList.add('d-none');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50');
            start.classList.remove('is-invalid');
        }

        end.min = start.value;
        if (!end.value) return;
        const endDate = new Date(`${end.value}T00:00:00`);
        const days = Math.round((endDate - startDate) / 86400000) + 1;
        info.classList.remove('d-none');
        info.textContent = days > 0 ? `Durasi peminjaman: ${days} Hari` : 'Tanggal selesai harus sama atau setelah tanggal mulai.';
        info.classList.toggle('alert-danger', days <= 0);
        info.classList.toggle('alert-info', days > 0);
    };

    start.addEventListener('change', updateValidation);
    end.addEventListener('change', updateValidation);
    updateValidation();
});
</script>
@endpush

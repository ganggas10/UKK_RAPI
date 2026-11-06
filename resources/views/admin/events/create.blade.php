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
                               value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Selesai Peminjaman <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                        @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12"><div id="duration_info" class="alert alert-info py-2 mb-0 d-none"></div></div>
                </div>

                <div class="row mb-4">
                    @foreach(['start_session' => 'Sesi Mulai', 'end_session' => 'Sesi Selesai'] as $field => $label)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ $label }} <span class="text-danger">*</span></label>
                            <select name="{{ $field }}" class="form-select @error($field) is-invalid @enderror" required>
                                <option value="">-- Pilih Sesi --</option>
                                @foreach(['Sesi 1' => '07:15 - 08:00', 'Sesi 2' => '08:00 - 08:45', 'Sesi 3' => '08:45 - 09:30', 'Sesi 4' => '09:30 - 10:15', 'Sesi 5' => '10:15 - 11:00', 'Sesi 6' => '11:00 - 11:45', 'Sesi 7' => '11:45 - 12:30', 'Sesi 8' => '12:30 - 13:15', 'Sesi 9' => '13:15 - 14:00', 'Sesi 10' => '14:00 - 14:45', 'Sesi 11' => '14:45 - 15:30'] as $session => $time)
                                    <option value="{{ $session }}" @selected(old($field) === $session)>{{ $session }} ({{ $time }})</option>
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
                    <button type="submit" class="btn btn-primary">
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
    const updateDuration = function () {
        if (!start.value) return;
        end.min = start.value;
        if (!end.value) return;
        const startDate = new Date(`${start.value}T00:00:00`);
        const endDate = new Date(`${end.value}T00:00:00`);
        const days = Math.round((endDate - startDate) / 86400000) + 1;
        info.classList.remove('d-none');
        info.textContent = days > 0 ? `Durasi peminjaman: ${days} Hari` : 'Tanggal selesai harus sama atau setelah tanggal mulai.';
        info.classList.toggle('alert-danger', days <= 0);
        info.classList.toggle('alert-info', days > 0);
    };
    start.addEventListener('change', updateDuration);
    end.addEventListener('change', updateDuration);
    updateDuration();
});
</script>
@endpush

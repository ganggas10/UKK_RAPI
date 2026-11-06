@extends('layouts.app')

@section('title', 'Lapor Kerusakan')

@section('content')
<div class="container py-4" style="max-width: 680px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        <h2 class="fw-bold mb-0">Lapor Kerusakan Fasilitas</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted">Laporan ini akan diteruskan otomatis kepada PIC ruangan yang dipilih.</p>
            <form action="{{ route('admin.complaints.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="room_id" class="form-label fw-semibold">Ruangan <span class="text-danger">*</span></label>
                    <select id="room_id" name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected(old('room_id') == $room->id)>{{ $room->name }}{{ $room->location ? ' - '.$room->location : '' }}</option>
                        @endforeach
                    </select>
                    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Deskripsi Kerusakan <span class="text-danger">*</span></label>
                    <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label for="photo" class="form-label fw-semibold">Foto Bukti Kerusakan <span class="text-danger">*</span></label>
                    <input id="photo" type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" capture="environment" required>
                    <small class="text-muted">Format JPG, PNG, atau WebP. Maksimal 5MB.</small>
                    @error('photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.complaints.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-danger">Kirim Laporan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
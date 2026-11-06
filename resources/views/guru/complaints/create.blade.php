@extends('layouts.app')

@section('title', 'Laporkan Kerusakan')

@section('content')
<div class="container py-4" style="max-width: 680px;">
    <h2 class="fw-bold mb-4">Laporkan Kerusakan Fasilitas</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            {{-- PENTING: enctype multipart untuk upload file --}}
            <form action="{{ route('guru.complaints.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ruangan yang Rusak <span class="text-danger">*</span></label>
                    <select name="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach($rooms->groupBy('location') as $category => $categoryRooms)
                            <optgroup label="{{ $category ?: 'Lainnya' }}">
                                @foreach($categoryRooms->sortBy('name') as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id', request('room_id')) == $room->id ? 'selected' : '' }}>
                                        {{ $room->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi Kerusakan <span class="text-danger">*</span></label>
                    <textarea name="description" rows="4"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Jelaskan kerusakan secara detail, contoh: AC ruangan tidak berfungsi sejak ...">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Upload Foto — Support kamera mobile (capture) --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Foto Bukti Kerusakan <span class="text-danger">*</span></label>
                    <div class="border border-2 border-dashed rounded p-3 text-center bg-light">
                        <input type="file" name="photo" id="photoInput"
                               class="form-control @error('photo') is-invalid @enderror"
                               accept="image/*"
                               required
                               capture="environment">
                        {{-- capture="environment" → langsung buka kamera belakang di HP --}}
                        <small class="text-muted d-block mt-2">
                            Bisa langsung foto dari kamera HP atau pilih dari galeri.<br>
                            Format: JPG, PNG, WebP | Maks: 5MB
                        </small>
                        @error('photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    {{-- Preview foto sebelum upload --}}
                    <div id="photoPreview" class="mt-2 d-none">
                        <p class="text-muted small mb-1">Preview:</p>
                        <img id="previewImg" src="#" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-danger btn-lg">
                        Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Preview foto sebelum upload
    document.getElementById('photoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('photoPreview').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
@endsection
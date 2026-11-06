@extends('layouts.app')

@section('title', 'Edit Ruangan')

@section('content')
<div class="container py-4" style="max-width: 640px;">
    <h2 class="fw-bold mb-4">✏️ Edit Ruangan</h2>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.rooms.update', $room) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Ruangan <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $room->name) }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Lokasi</label>
                    <input type="text" name="location" class="form-control"
                           value="{{ old('location', $room->location) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Kapasitas (orang) <span class="text-danger">*</span></label>
                    <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror"
                           value="{{ old('capacity', $room->capacity) }}" min="1">
                    @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning px-4">Update</button>
                    <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
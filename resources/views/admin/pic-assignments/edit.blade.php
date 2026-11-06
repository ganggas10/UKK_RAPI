@extends('layouts.app')
@section('title', 'Ganti PIC Ruang')
@section('content')
<div class="container py-4" style="max-width:720px"><h2 class="fw-bold mb-1">Ganti PIC Ruang</h2><p class="text-muted mb-4">{{ $assignment->room->name }} saat ini ditangani {{ $assignment->pic?->name }}.</p><div class="card shadow-sm"><div class="card-body"><form method="POST" action="{{ route('admin.pic-assignments.update', $assignment) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label fw-semibold">PIC Pengganti</label><select name="pic_id" class="form-select" required>@foreach($pics as $pic)<option value="{{ $pic->id }}" @selected(old('pic_id', $assignment->pic_id) == $pic->id)>{{ $pic->name }} - {{ $pic->email }}</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label fw-semibold">Tanggal Mulai</label><input type="date" name="starts_at" value="{{ old('starts_at', now()->toDateString()) }}" class="form-control" required></div>
<div class="mb-3"><label class="form-label fw-semibold">Alasan Penggantian</label><textarea name="replacement_reason" class="form-control" rows="3" required>{{ old('replacement_reason') }}</textarea>@error('replacement_reason')<div class="text-danger small">{{ $message }}</div>@enderror</div>
<div class="mb-4"><label class="form-label fw-semibold">Catatan PIC Baru</label><textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea></div><button class="btn btn-warning">Simpan Perubahan</button> <a href="{{ route('admin.pic-assignments.index') }}" class="btn btn-outline-secondary">Batal</a></form></div></div></div>
@endsection

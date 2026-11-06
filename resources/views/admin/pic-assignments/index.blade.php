@extends('layouts.app')

@section('title', 'Manajemen PIC Ruang')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h2 class="fw-bold mb-1">Manajemen PIC Ruang</h2><p class="text-muted mb-0">Kelola pembagian tanggung jawab PIC terhadap setiap ruangan sekolah.</p></div>
        <a href="{{ route('admin.pic-assignments.create') }}" class="btn btn-primary">+ Tambah Penugasan PIC</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="row g-3 mb-4">
        @foreach([
            ['Total Ruangan', $stats['rooms'], 'pastel-blue', 'bi-door-open text-primary'],
            ['Total PIC', $stats['pics'], 'pastel-purple', 'bi-people text-primary'],
            ['Sudah Memiliki PIC', $stats['assigned'], 'pastel-green', 'bi-check2-circle text-success'],
            ['Belum Memiliki PIC', $stats['unassigned'], 'pastel-yellow', 'bi-exclamation-circle text-warning'],
        ] as [$label, $value, $pastelClass, $iconClass])
            <div class="col-6 col-lg-3">
                <div class="rapi-asset-card {{ $pastelClass }}">
                    <div>
                        <div class="rapi-asset-value">{{ $value }}</div>
                        <div class="rapi-asset-label">{{ $label }}</div>
                    </div>
                    <div class="mt-3">
                        <span class="rapi-asset-icon"><i class="bi {{ $iconClass }}"></i></span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="card shadow-sm mb-3"><div class="card-body"><form class="row g-2" method="GET"><div class="col-md-4"><input name="search" value="{{ request('search') }}" class="form-control" placeholder="Cari ruangan atau PIC"></div><div class="col-md-2"><select name="category" class="form-select"><option value="">Semua Kategori</option>@foreach($categories as $category)<option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>@endforeach</select></div><div class="col-md-2"><select name="pic_id" class="form-select"><option value="">Semua PIC</option>@foreach($pics as $pic)<option value="{{ $pic->id }}" @selected(request('pic_id') == $pic->id)>{{ $pic->name }}</option>@endforeach</select></div><div class="col-md-2"><select name="status" class="form-select"><option value="">Semua Status</option><option value="assigned" @selected(request('status') === 'assigned')>Sudah Ditugaskan</option><option value="unassigned" @selected(request('status') === 'unassigned')>Belum Ditugaskan</option></select></div><div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-grow-1">Filter</button><a href="{{ route('admin.pic-assignments.index') }}" class="btn btn-outline-secondary">Reset</a></div></form></div></div>
    <div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-dark"><tr><th>No.</th><th>Ruangan</th><th>Lokasi</th><th>PIC</th><th>Email</th><th>HP</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
    @forelse($rooms as $room)<tr><td>{{ $rooms->firstItem() + $loop->index }}</td><td class="fw-semibold">{{ $room->name }}</td><td>{{ $room->location ?: '-' }}</td><td>{{ $room->pic?->name ?: '-' }}</td><td>{{ $room->pic?->email ?: '-' }}</td><td>{{ $room->pic?->phone ?: '-' }}</td><td>@if($room->pic)<span class="badge text-bg-success">Sudah Ditugaskan</span>@else<span class="badge text-bg-warning">Belum Ditugaskan</span>@endif</td><td class="text-nowrap">@if($room->pic && $room->pic->picAssignments()->where('status', 'aktif')->exists()) @php($assignment = $room->picAssignments()->where('status', 'aktif')->latest('id')->first()) <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.pic-assignments.show', $assignment) }}">Detail</a><a class="btn btn-sm btn-warning" href="{{ route('admin.pic-assignments.edit', $assignment) }}">Edit</a><form class="d-inline" method="POST" action="{{ route('admin.pic-assignments.destroy', $assignment) }}" onsubmit="return confirm('Hentikan penugasan PIC ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus</button></form>@else<a class="btn btn-sm btn-primary" href="{{ route('admin.pic-assignments.create', ['room_id' => $room->id]) }}">Tugaskan</a>@endif</td></tr>@empty<tr><td colspan="8" class="text-center text-muted py-4">Belum ada data ruangan.</td></tr>@endforelse
    </tbody></table></div></div><div class="mt-3">{{ $rooms->links() }}</div><a href="{{ route('admin.pic-assignments.history') }}" class="btn btn-link px-0">Lihat riwayat penugasan</a>
</div>
@endsection

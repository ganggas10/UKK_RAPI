@extends('layouts.app')

@section('title', 'Manajemen Ruangan')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Manajemen Ruangan</h2>
        <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
            Tambah Ruangan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nama Ruangan</th>
                        <th>Lokasi</th>
                        <th>Kapasitas</th>
                        <th>PIC Ruang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rooms as $room)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td><strong>{{ $room->name }}</strong></td>
                        <td>{{ $room->location ?? '-' }}</td>
                        <td>{{ $room->capacity }} orang</td>
                        <td>
                            @if($room->pic)
                                <span class="badge bg-info text-dark">{{ $room->pic->name }}</span>
                            @else
                                <span class="badge bg-secondary">Belum ada PIC</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.rooms.destroy', $room) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus ruangan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada ruangan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $rooms->links() }}</div>
</div>
@endsection
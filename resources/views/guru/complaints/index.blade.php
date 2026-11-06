@extends('layouts.app')

@section('title', 'Laporan Kerusakan Saya')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Laporan Kerusakan Saya</h2>
        <a href="{{ route('guru.complaints.create') }}" class="btn btn-danger">
            Lapor Kerusakan
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-danger">
                    <tr>
                        <th>Ruangan</th>
                        <th>Deskripsi</th>
                        <th>Foto</th>
                        <th>Status</th>
                        <th>Catatan PIC</th>
                        <th>Dilaporkan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($complaints as $complaint)
                    <tr>
                        <td>{{ $complaint->room->name }}</td>
                        <td>{{ Str::limit($complaint->description, 60) }}</td>
                        <td>
                            @if($complaint->photo_path)
                                <a href="{{ Storage::url($complaint->photo_path) }}" target="_blank">
                                    <img src="{{ Storage::url($complaint->photo_path) }}"
                                         alt="Foto" style="height:50px; object-fit:cover;" class="rounded">
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badge = match($complaint->status_repair) {
                                    'Sudah Diperbaiki'   => 'success',
                                    'Menunggu Spare Part'=> 'warning text-dark',
                                    default              => 'danger',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $complaint->status_repair }}</span>
                        </td>
                        <td>{{ $complaint->pic_notes ?? '-' }}</td>
                        <td>{{ $complaint->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('guru.complaints.show', $complaint) }}" class="btn btn-xs btn-outline-danger">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada laporan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
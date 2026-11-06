@extends('layouts.app')

@section('title', 'Peminjaman Saya')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Peminjaman Ruang Saya</h2>
        <a href="{{ route('guru.events.create') }}" class="btn btn-primary">
            Ajukan Baru
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-primary">
                    <tr>
                        <th>Judul</th>
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr>
                        <td>
                            <strong>{{ $event->title }}</strong>
                            <div>
                                <span class="badge" style="background-color: {{ $event->creatorColor() }}; color: #ffffff; font-size: 0.72rem;">
                                    {{ $event->creatorRoleLabel() }}
                                </span>
                            </div>
                        </td>
                        <td>
                            {{ $event->room->name }}
                            @if($event->status === 'moved')
                                <br><small class="text-info">→ Dipindah ke: {{ $event->movedToRoom->name ?? '-' }}</small>
                            @endif
                        </td>
                        <td>{{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }}<br><small class="text-muted">{{ $event->bookingDurationInDays() }} hari</small></td>
                        <td>{{ $event->session ?: substr($event->start_time,0,5).'–'.substr($event->end_time,0,5) }}</td>
                        <td>
                            @php
                                $badge = match($event->status) {
                                    'approved' => 'success',
                                    'pending'  => 'warning text-dark',
                                    'rejected' => 'danger',
                                    'moved'    => 'info',
                                    default    => 'secondary'
                                };
                                $label = match($event->status) {
                                    'approved' => 'Disetujui',
                                    'pending'  => 'Menunggu',
                                    'rejected' => 'Ditolak',
                                    'moved'    => 'Dipindah',
                                    default    => 'Unknown'
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                        </td>
                        <td>
                            @if($event->move_reason)
                                <small class="text-warning">{{ Str::limit($event->move_reason, 50) }}</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('guru.events.show', $event) }}" class="btn btn-xs btn-outline-primary">
                                Detail
                            </a>
                            <a href="{{ route('guru.complaints.create', ['room_id' => $event->room_id]) }}" class="btn btn-xs btn-outline-danger">
                                Lapor Kerusakan
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pengajuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Manajemen Agenda')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Manajemen Agenda & Peminjaman</h2>
        <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
            Tambah Agenda
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Judul</th>
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Pengaju</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($events as $event)
                    <tr>
                        <td><strong>{{ $event->title }}</strong></td>
                        <td>{{ $event->room->name }}</td>
                        <td>{{ $event->bookingStartDate()->format('d/m/Y') }}–{{ $event->bookingEndDate()->format('d/m/Y') }}<br><small class="text-muted">{{ $event->bookingDurationInDays() }} hari</small></td>
                        <td>{{ $event->session ?: substr($event->start_time,0,5).'–'.substr($event->end_time,0,5) }}</td>
                        <td>
                            <div>{{ $event->user->name }}</div>
                            <span class="badge" style="background-color: {{ $event->creatorColor() }}; color: #ffffff;">
                                {{ $event->creatorRoleLabel() }}
                            </span>
                        </td>
                        <td>
                            @php
                                $badge = match($event->status) {
                                    'approved' => 'success',
                                    'pending'  => 'warning',
                                    'rejected' => 'danger',
                                    'moved'    => 'info',
                                    default    => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ ucfirst($event->status) }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                {{-- Setujui/Tolak untuk event pending --}}
                                @if($event->status === 'pending')
                                <form action="{{ route('admin.events.update', $event) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-xs btn-success">Setujui</button>
                                </form>
                                <form action="{{ route('admin.events.update', $event) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="text" name="rejection_message" class="form-control form-control-sm mb-1" placeholder="Pesan penolakan" required>
                                    <textarea name="rejection_notes" class="form-control form-control-sm mb-1" rows="2" placeholder="Catatan/alasan penolakan" required></textarea>
                                    <button class="btn btn-xs btn-danger">Tolak</button>
                                </form>
                                @endif
                                {{-- Pindah Ruang (Override) --}}
                                @if(in_array($event->status, ['pending', 'approved']))
                                <button class="btn btn-xs btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#moveModal{{ $event->id }}">
                                    Pindah
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada agenda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal diletakkan di luar tabel agar struktur HTML dan stacking Bootstrap valid. --}}
    @foreach($events as $event)
        @if(in_array($event->status, ['pending', 'approved']))
        <div class="modal fade" id="moveModal{{ $event->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Pindah Ruangan: {{ $event->title }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.events.move', $event) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted">Ruangan saat ini: <strong>{{ $event->room->name }}</strong></p>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pindah ke Ruangan <span class="text-danger">*</span></label>
                                <select name="new_room_id" class="form-select" required>
                                    <option value="">-- Pilih Ruangan Baru --</option>
                                    @foreach(\App\Models\Room::where('id', '!=', $event->room_id)->orderBy('name')->get() as $room)
                                        <option value="{{ $room->id }}">{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Alasan Pemindahan <span class="text-danger">*</span></label>
                                <textarea name="move_reason" class="form-control" rows="3"
                                    placeholder="Contoh: Ada rapat mendadak kepala sekolah di ruang tersebut..." required minlength="10"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning">Pindahkan & Kirim Notifikasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endforeach

    <div class="mt-3">{{ $events->links() }}</div>
</div>
@endsection
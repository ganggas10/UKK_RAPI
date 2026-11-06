@extends('layouts.app')

@section('title', 'Kelola Hari Penting & Libur Nasional')

@section('content')
<div class="container-fluid py-4 px-3 px-lg-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <p class="text-primary fw-semibold mb-1">PENGATURAN KALENDER NASIONAL</p>
            <h2 class="fw-bold mb-1">Hari Penting & Libur Nasional</h2>
            <p class="text-muted mb-0">Kelola daftar hari libur nasional, hari besar keagamaan, dan hari peringatan penting Indonesia.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.national-holidays.create') }}" class="btn btn-primary">
                ➕ Tambah Hari Penting
            </a>
            <form action="{{ route('admin.national-holidays.sync') }}" method="POST" onsubmit="return confirm('Perbarui data resmi kalender nasional (SKB 3 Menteri)? Data bawaan akan disinkronkan tanpa menghapus data baru.')">
                @csrf
                <button type="submit" class="btn btn-outline-success">
                    🔄 Sinkronisasi Data Resmi
                </button>
            </form>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.national-holidays.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Cari Nama Hari Penting</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Contoh: Kemerdekaan, Idulfitri, Pahlawan...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tahun</label>
                    <select name="year" class="form-select">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $yr)
                            <option value="{{ $yr }}" @selected((string)$yearFilter === (string)$yr)>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $key => $lbl)
                            <option value="{{ $key }}" @selected($categoryFilter === $key)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                    <a href="{{ route('admin.national-holidays.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card shadow-sm">
        <div class="card-body border-bottom d-flex justify-content-between align-items-center">
            <strong>{{ number_format($holidays->total()) }}</strong> hari penting terdaftar
            <a href="{{ route('calendar.public') }}" target="_blank" class="btn btn-sm btn-outline-primary">
                Buka Kalender Publik ↗
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Tanggal</th>
                        <th>Nama Hari Penting</th>
                        <th>Kategori</th>
                        <th>Status Libur</th>
                        <th>Sumber</th>
                        <th style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $item)
                        <tr>
                            <td>{{ $holidays->firstItem() + $loop->index }}</td>
                            <td>
                                <strong>{{ $item->date->format('d/m/Y') }}</strong><br>
                                <small class="text-muted">{{ $item->date->translatedFormat('l') }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background-color:{{ $item->color }};"></span>
                                    <div>
                                        <div class="fw-bold">{{ $item->name }}</div>
                                        @if($item->description)
                                            <small class="text-muted">{{ Str::limit($item->description, 65) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: {{ $item->color }}; color: {{ $item->text_color }};">
                                    {{ $item->category_label }}
                                </span>
                            </td>
                            <td>
                                @if($item->is_holiday)
                                    <span class="badge bg-danger">🔴 Libur Nasional</span>
                                @else
                                    <span class="badge bg-secondary">Hari Peringatan</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $item->source ?: '-' }}</small></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.national-holidays.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('admin.national-holidays.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus hari penting {{ $item->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Tidak ada data hari penting yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($holidays->hasPages())
            <div class="card-footer bg-white">
                {{ $holidays->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Edit Hari Penting / Libur Nasional')

@section('content')
<div class="container py-4" style="max-width: 680px;">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.national-holidays.index') }}" class="btn btn-outline-secondary btn-sm">
            Kembali
        </a>
        <h2 class="fw-bold mb-0">✏️ Edit Hari Penting Nasional</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.national-holidays.update', $nationalHoliday) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Hari Penting / Libur *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $nationalHoliday->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal *</label>
                        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date', $nationalHoliday->date->format('Y-m-d')) }}" required>
                        @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kategori *</label>
                        <select name="category" id="categorySelect" class="form-select @error('category') is-invalid @enderror" required>
                            @foreach($categories as $key => $lbl)
                                <option value="{{ $key }}" @selected(old('category', $nationalHoliday->category) === $key)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Warna Penanda</label>
                        <div class="input-group">
                            <input type="color" id="colorPicker" name="color" class="form-control form-control-color"
                                   value="{{ old('color', $nationalHoliday->color) }}" title="Pilih warna">
                            <input type="text" id="colorInput" class="form-control" value="{{ old('color', $nationalHoliday->color) }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sumber Referensi Resmi</label>
                        <input type="text" name="source" class="form-control"
                               value="{{ old('source', $nationalHoliday->source) }}" placeholder="Contoh: SKB 3 Menteri / Keppres">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Deskripsi / Keterangan</label>
                    <textarea name="description" rows="3" class="form-control">{{ old('description', $nationalHoliday->description) }}</textarea>
                </div>

                <div class="form-check form-switch mb-4 p-3 bg-light rounded border">
                    <input type="hidden" name="is_holiday" value="0">
                    <input class="form-check-input ms-0 me-3" type="checkbox" role="switch" id="is_holiday" name="is_holiday" value="1" @checked(old('is_holiday', $nationalHoliday->is_holiday))>
                    <label class="form-check-label fw-semibold" for="is_holiday">
                        Tandai sebagai Hari Libur Nasional (Tanggal Merah)
                    </label>
                    <div class="text-muted small ps-4">Jika dicentang, tanggal ini dianggap sebagai hari libur resmi.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning px-4">Perbarui</button>
                    <a href="{{ route('admin.national-holidays.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const defaultColors = @json($defaultColors);
    const categorySelect = document.getElementById('categorySelect');
    const colorPicker = document.getElementById('colorPicker');
    const colorInput = document.getElementById('colorInput');

    categorySelect.addEventListener('change', function() {
        const cat = this.value;
        if (defaultColors[cat]) {
            colorPicker.value = defaultColors[cat];
            colorInput.value = defaultColors[cat];
        }
    });

    colorPicker.addEventListener('input', function() {
        colorInput.value = this.value;
    });
</script>
@endsection


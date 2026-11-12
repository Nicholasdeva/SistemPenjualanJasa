@extends('layouts.app')

@php
    $editing = isset($service);
    $tiers = \App\Http\Requests\Seller\StoreServiceRequest::TIERS;
    $status = old('status', $service->status ?? 'published');

    // Tab yang punya error dibuka otomatis
    $activeTab = 0;
    foreach ($tiers as $i => $t) {
        if ($errors->has("packages.$i.*")) { $activeTab = $i; break; }
    }
@endphp

@section('title', $editing ? 'Edit Jasa' : 'Tambah Jasa')

@section('content')
<div class="mb-4">
    <a href="{{ route('seller.services.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Jasa Saya</a>
    <h1 class="h4 fw-bold mt-2 mb-1">{{ $editing ? 'Edit Jasa' : 'Tambah Jasa' }}</h1>
    <p class="text-secondary small mb-0">Isi informasi jasa dan atur harga untuk setiap paket.</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger small">
        Ada {{ $errors->count() }} isian yang perlu diperbaiki. Periksa kolom yang ditandai merah.
        @error('packages')<div class="mt-1 fw-semibold">{{ $message }}</div>@enderror
    </div>
@endif

<form action="{{ $editing ? route('seller.services.update', $service) : route('seller.services.store') }}"
      method="POST" enctype="multipart/form-data" novalidate>
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-semibold mb-0">Informasi jasa</h2>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="title" class="form-label small fw-semibold">Judul</label>
                        <input type="text" id="title" name="title" maxlength="120" class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $service->title ?? '') }}" placeholder="Contoh: Desain Logo Minimalis untuk UMKM" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label for="category_id" class="form-label small fw-semibold">Kategori</label>
                        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="" disabled @selected(! old('category_id', $service->category_id ?? null))>Pilih kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((int) old('category_id', $service->category_id ?? 0) === $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label for="description" class="form-label small fw-semibold">Deskripsi</label>
                        <textarea id="description" name="description" rows="6" class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Jelaskan apa yang pembeli dapatkan dari jasa ini." required>{{ old('description', $service->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Minimal 30 karakter.</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white pt-3 pb-0">
                    <h2 class="h6 fw-semibold mb-3">Paket</h2>
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        @foreach ($tiers as $i => $tier)
                            <li class="nav-item" role="presentation">
                                <button type="button" class="nav-link {{ $i === $activeTab ? 'active' : '' }} {{ $errors->has("packages.$i.*") ? 'text-danger' : '' }}"
                                        data-bs-toggle="tab" data-bs-target="#tab-{{ $i }}">
                                    {{ $tier }}@if ($tier === 'Basic')<span class="text-danger">*</span>@endif
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="card-body p-4 tab-content">
                    @foreach ($tiers as $i => $tier)
                        @php $pkg = $editing ? $service->packages->firstWhere('tier_name', $tier) : null; @endphp
                        <div class="tab-pane fade {{ $i === $activeTab ? 'show active' : '' }}" id="tab-{{ $i }}">
                            <input type="hidden" name="packages[{{ $i }}][tier_name]" value="{{ $tier }}">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Harga</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" min="1" step="1000" name="packages[{{ $i }}][price]"
                                               class="form-control @error("packages.$i.price") is-invalid @enderror"
                                               value="{{ old("packages.$i.price", $pkg ? (int) $pkg->price : '') }}">
                                        @error("packages.$i.price")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Lama pengerjaan</label>
                                    <div class="input-group has-validation">
                                        <input type="number" min="1" name="packages[{{ $i }}][delivery_days]"
                                               class="form-control @error("packages.$i.delivery_days") is-invalid @enderror"
                                               value="{{ old("packages.$i.delivery_days", $pkg->delivery_days ?? '') }}">
                                        <span class="input-group-text">hari</span>
                                        @error("packages.$i.delivery_days")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold">Kuota revisi</label>
                                    <div class="input-group has-validation">
                                        <input type="number" min="0" name="packages[{{ $i }}][revision_limit]"
                                               class="form-control @error("packages.$i.revision_limit") is-invalid @enderror"
                                               value="{{ old("packages.$i.revision_limit", $pkg->revision_limit ?? '') }}">
                                        <span class="input-group-text">kali</span>
                                        @error("packages.$i.revision_limit")<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-white small text-secondary">
                    Paket Basic wajib diisi. Kosongkan harga Standard atau Premium jika tidak ingin menawarkannya.
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-semibold mb-0">Gambar sampul</h2>
                </div>
                <div class="card-body p-4">
                    <div class="ratio ratio-16x9 rounded-3 border bg-light mb-3 overflow-hidden">
                        <img id="thumb-preview" src="{{ $editing && $service->thumbnail ? $service->thumbnail : '' }}"
                             alt="" class="w-100 h-100 object-fit-cover {{ $editing && $service->thumbnail ? '' : 'd-none' }}">
                        <div id="thumb-empty" class="d-flex flex-column align-items-center justify-content-center text-secondary small {{ $editing && $service->thumbnail ? 'd-none' : '' }}">
                            <i class="bi bi-image fs-3 mb-1"></i>Belum ada gambar
                        </div>
                    </div>

                    <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png,image/webp"
                           class="form-control form-control-sm @error('thumbnail') is-invalid @enderror">
                    @error('thumbnail')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="form-text">JPG, PNG, atau WEBP. Maksimal 2 MB. Rasio 16:9 paling pas.</div>
                    @enderror

                    @if ($editing && $service->thumbnail)
                        <div class="form-check mt-2">
                            <input type="checkbox" class="form-check-input" id="remove_thumbnail" name="remove_thumbnail" value="1" @checked(old('remove_thumbnail'))>
                            <label class="form-check-label small" for="remove_thumbnail">Hapus gambar sampul</label>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <label class="form-label small fw-semibold">Status</label>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="status-published" value="published" @checked($status === 'published')>
                            <label class="btn btn-outline-primary w-100" for="status-published">Tayang</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" class="btn-check" name="status" id="status-draft" value="draft" @checked($status === 'draft')>
                            <label class="btn btn-outline-primary w-100" for="status-draft">Draft</label>
                        </div>
                    </div>
                    @error('status')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                    <p class="small text-secondary mb-4">
                        Jasa berstatus tayang muncul di katalog setelah akunmu diverifikasi admin.
                    </p>

                    <button type="submit" class="btn btn-primary w-100 mb-2">{{ $editing ? 'Simpan Perubahan' : 'Simpan Jasa' }}</button>
                    <a href="{{ route('seller.services.index') }}" class="btn btn-outline-secondary w-100">Batal</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    // Pratinjau gambar sampul sebelum diunggah
    document.getElementById('thumbnail')?.addEventListener('change', function (e) {
        const file = e.target.files[0];
        const img = document.getElementById('thumb-preview');
        const empty = document.getElementById('thumb-empty');
        if (!file) return;
        img.src = URL.createObjectURL(file);
        img.classList.remove('d-none');
        empty.classList.add('d-none');
    });
</script>
@endpush

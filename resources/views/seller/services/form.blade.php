@extends('layouts.app')

@section('content')
@php
    $editing = isset($service);
    $tiers = ['Basic', 'Standard', 'Premium'];
@endphp

<h1 class="h3 mb-4">{{ $editing ? 'Edit Jasa' : 'Tambah Jasa' }}</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ $editing ? route('seller.services.update', $service) : route('seller.services.store') }}" method="POST">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label">Judul</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $service->title ?? '') }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Kategori</label>
        <select name="category_id" class="form-select" required>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((int) old('category_id', $service->category_id ?? 0) === $cat->id)>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control" rows="4" required>{{ old('description', $service->description ?? '') }}</textarea>
    </div>

    <h5>Paket</h5>
    <ul class="nav nav-tabs" role="tablist">
        @foreach ($tiers as $i => $tier)
            <li class="nav-item" role="presentation">
                <button type="button" class="nav-link {{ $i === 0 ? 'active' : '' }}"
                        data-bs-toggle="tab" data-bs-target="#tab-{{ $i }}">{{ $tier }}</button>
            </li>
        @endforeach
    </ul>
    <div class="tab-content border border-top-0 bg-white p-3 mb-4">
        @foreach ($tiers as $i => $tier)
            @php $pkg = $editing ? $service->packages->firstWhere('tier_name', $tier) : null; @endphp
            <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="tab-{{ $i }}">
                <input type="hidden" name="packages[{{ $i }}][tier_name]" value="{{ $tier }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Harga (Rp)</label>
                        <input type="number" min="0" step="1000" name="packages[{{ $i }}][price]" class="form-control"
                               value="{{ old('packages.'.$i.'.price', $pkg->price ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hari Pengerjaan</label>
                        <input type="number" min="0" name="packages[{{ $i }}][delivery_days]" class="form-control"
                               value="{{ old('packages.'.$i.'.delivery_days', $pkg->delivery_days ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kuota Revisi</label>
                        <input type="number" min="0" name="packages[{{ $i }}][revision_limit]" class="form-control"
                               value="{{ old('packages.'.$i.'.revision_limit', $pkg->revision_limit ?? '') }}">
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="submit" class="btn btn-success">{{ $editing ? 'Update' : 'Simpan' }}</button>
    <a href="{{ route('seller.services.index') }}" class="btn btn-outline-secondary">Batal</a>
</form>
@endsection
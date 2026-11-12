@extends('layouts.app')

@section('title', 'Katalog Jasa')
@section('hide_sidebar', true)

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Katalog Jasa</h1>
    <p class="text-secondary small mb-0">Temukan jasa yang sesuai kebutuhan kamu dari penjual terverifikasi.</p>
</div>

<div class="row g-4">
    <aside class="col-lg-3">
        <form action="{{ route('catalog') }}" method="GET" class="card">
            <div class="card-body">
                <label for="q" class="form-label small fw-semibold">Cari jasa</label>
                <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control mb-3" placeholder="Contoh: desain logo">

                <label for="category" class="form-label small fw-semibold">Kategori</label>
                <select id="category" name="category" class="form-select mb-3">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <label for="sort" class="form-label small fw-semibold">Urutkan</label>
                <select id="sort" name="sort" class="form-select mb-3">
                    <option value="latest" @selected(request('sort', 'latest') === 'latest')>Terbaru</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Harga terendah</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Harga tertinggi</option>
                </select>

                <button type="submit" class="btn btn-primary w-100">Terapkan</button>
            </div>
        </form>
    </aside>

    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="small text-secondary">{{ $services->total() }} jasa ditemukan</span>
        </div>

        @if ($services->isEmpty())
            <x-empty-state
                title="Belum ada jasa"
                description="Belum ada jasa yang ditayangkan saat ini."
                icon="bi-inbox"
            />
        @else
            <div class="row g-3">
                @foreach ($services as $service)
                    <div class="col-md-6 col-xl-4">
                        @include('public.partials.service-card', ['service' => $service])
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                {{ $services->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

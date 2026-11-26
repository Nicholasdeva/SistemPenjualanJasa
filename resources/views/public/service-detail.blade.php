@extends('layouts.app')

@section('title', $service->title)
@section('hide_sidebar', true)

@php
    $user = auth()->user();
    $sellerName = $service->seller->name ?? 'Penjual';
    $isVerified = (bool) optional($service->seller->sellerProfile)->is_verified;
    $avgRating = $service->reviews->avg('rating');
    $rupiah = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
@endphp

@section('content')
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb small mb-0">
        <li class="breadcrumb-item"><a href="{{ route('catalog') }}" class="text-decoration-none">Katalog</a></li>
        <li class="breadcrumb-item">{{ $service->category->name ?? 'Lainnya' }}</li>
        <li class="breadcrumb-item active text-truncate" aria-current="page">{{ \Illuminate\Support\Str::limit($service->title, 40) }}</li>
    </ol>
</nav>

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Judul & deskripsi --}}
        <div class="card mb-4 overflow-hidden">
            @if ($service->thumbnail)
                <div class="ratio ratio-16x9 border-bottom bg-light">
                    <img src="{{ $service->thumbnail }}" alt="{{ $service->title }}" class="object-fit-cover">
                </div>
            @endif
            <div class="card-body p-4">
                <div class="small text-primary fw-medium mb-2">{{ $service->category->name ?? 'Lainnya' }}</div>
                <h1 class="h4 fw-bold mb-2">{{ $service->title }}</h1>
                <div class="small text-secondary mb-4">
                    @if ($service->reviews->isNotEmpty())
                        <i class="bi bi-star-fill text-warning"></i>
                        <strong class="text-dark">{{ number_format($avgRating, 1, ',', '') }}</strong>
                        ({{ $service->reviews->count() }} ulasan)
                    @else
                        Belum ada ulasan
                    @endif
                    <span class="mx-2">·</span>oleh {{ $sellerName }}
                </div>

                <h2 class="h6 fw-semibold">Deskripsi</h2>
                <p class="text-secondary mb-0" style="white-space: pre-line;">{{ $service->description }}</p>
            </div>
        </div>

        {{-- Perbandingan paket --}}
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-semibold mb-0">Pilihan paket</h2>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start" style="width: 28%;"></th>
                            @foreach ($service->packages as $package)
                                <th>{{ $package->tier_name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start text-secondary small">Harga</td>
                            @foreach ($service->packages as $package)
                                <td class="mono">{{ $rupiah($package->price) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="text-start text-secondary small">Lama pengerjaan</td>
                            @foreach ($service->packages as $package)
                                <td>{{ $package->delivery_days }} hari</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="text-start text-secondary small">Kuota revisi</td>
                            @foreach ($service->packages as $package)
                                <td>{{ $package->revision_limit }}x</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td></td>
                            @foreach ($service->packages as $package)
                                <td>
                                    @if ($user?->role === 'buyer')
                                        <a href="{{ route('buyer.checkout', $package->id) }}" class="btn btn-sm btn-primary px-3">Pesan</a>
                                    @elseif (! $user)
                                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary px-3">Pesan</a>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary px-3" disabled title="Hanya akun pembeli yang bisa memesan">Pesan</button>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
            @if ($user && $user->role !== 'buyer')
                <div class="card-footer bg-white small text-secondary">Pemesanan hanya bisa dilakukan dengan akun pembeli.</div>
            @elseif (! $user)
                <div class="card-footer bg-white small text-secondary">Silakan <a href="{{ route('login') }}">login</a> sebagai pembeli untuk memesan.</div>
            @endif
        </div>

        {{-- Ulasan --}}
        <div class="card">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-semibold mb-0">Ulasan pembeli</h2>
            </div>
            <div class="card-body">
                @forelse ($service->reviews as $review)
                    <div class="{{ $loop->last ? '' : 'border-bottom pb-3 mb-3' }}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold small">{{ $review->order->buyer->name ?? 'Pembeli' }}</span>
                            <span class="small text-secondary">{{ $review->created_at?->translatedFormat('d M Y') }}</span>
                        </div>
                        <x-star-rating :rating="$review->rating" />
                        @if ($review->comment)
                            <p class="small text-secondary mb-0 mt-1">{{ $review->comment }}</p>
                        @endif
                    </div>
                @empty
                    <p class="small text-secondary mb-0">Belum ada ulasan untuk jasa ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Profil penjual --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-semibold"
                          style="width: 48px; height: 48px;">
                        {{ strtoupper(mb_substr($sellerName, 0, 1)) }}
                    </span>
                    <div>
                        <div class="fw-semibold">{{ $sellerName }}</div>
                        @if ($isVerified)
                            <span class="small text-success"><i class="bi bi-patch-check-fill me-1"></i>Terverifikasi</span>
                        @else
                            <span class="small text-secondary">Belum terverifikasi</span>
                        @endif
                    </div>
                </div>
                <dl class="row small mb-0">
                    <dt class="col-6 fw-normal text-secondary">Jasa aktif</dt>
                    <dd class="col-6 text-end mb-2">{{ $sellerStats['services'] }}</dd>
                    <dt class="col-6 fw-normal text-secondary">Bergabung</dt>
                    <dd class="col-6 text-end mb-0">{{ $sellerStats['joined']?->translatedFormat('M Y') }}</dd>
                </dl>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body small text-secondary">
                <i class="bi bi-shield-check text-primary me-1"></i>
                Pembayaran disimpan di escrow dan baru diteruskan ke penjual setelah kamu menyetujui hasil pekerjaan.
            </div>
        </div>
    </div>
</div>
@endsection

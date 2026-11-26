@extends('layouts.app')

@section('title', 'Dashboard Penjual')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1">Dashboard Penjual</h1>
        <p class="text-secondary small mb-0">Ringkasan pesanan dan saldo kamu.</p>
    </div>
    <a href="{{ route('seller.services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Jasa</a>
</div>

@unless ($isVerified)
    <div class="alert alert-warning d-flex align-items-center gap-2 small">
        <i class="bi bi-exclamation-circle"></i>
        <div>
            Akun kamu belum diverifikasi admin, sehingga jasa belum tampil di katalog.
            <a href="{{ route('seller.profile.edit') }}" class="alert-link">Lengkapi profil</a>
        </div>
    </div>
@endunless

@php
    $cards = [
        ['Pesanan aktif', $activeOrders, 'bi-hourglass-split', route('seller.orders.index')],
        ['Saldo wallet', 'Rp' . number_format($walletBalance, 0, ',', '.'), 'bi-wallet2', route('seller.withdrawals.index')],
        ['Saldo escrow', 'Rp' . number_format($escrowBalance, 0, ',', '.'), 'bi-shield-lock', null],
        ['Rating rata-rata', $averageRating ? number_format($averageRating, 1, ',', '') . ' / 5' : '-', 'bi-star', null],
    ];
@endphp

<div class="row g-3">
    @foreach ($cards as [$label, $value, $icon, $link])
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="small text-secondary fw-medium">{{ $label }}</span>
                        <span class="icon-tile" style="width:36px;height:36px;font-size:1rem;"><i class="bi {{ $icon }}"></i></span>
                    </div>
                    <div class="fs-4 fw-semibold text-dark">{{ $value }}</div>
                    @if ($link)
                        <a href="{{ $link }}" class="small text-decoration-none">Lihat detail</a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card mt-4">
    <div class="card-body small text-secondary">
        <i class="bi bi-info-circle text-primary me-1"></i>
        Saldo escrow adalah dana dari pesanan yang masih berjalan. Dana pindah ke saldo wallet setelah pembeli menyetujui hasil kerja.
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Dashboard Admin</h1>
    <p class="text-secondary small mb-0">Ringkasan aktivitas marketplace.</p>
</div>

@php
    $cards = [
        ['Total pengguna', $stats['users'], 'bi-people', null],
        ['Penjual belum terverifikasi', $stats['sellers_pending'], 'bi-person-check', route('admin.sellers.index')],
        ['Total jasa', $stats['services'], 'bi-grid', route('admin.services.index')],
        ['Total pesanan', $stats['orders'], 'bi-bag', null],
        ['Dana di escrow', 'Rp' . number_format($stats['escrow_total'], 0, ',', '.'), 'bi-shield-lock', null],
        ['Penarikan menunggu', $stats['withdrawals_pending'], 'bi-cash-coin', route('admin.withdrawals.index')],
    ];
@endphp

<div class="row g-3">
    @foreach ($cards as [$label, $value, $icon, $link])
        <div class="col-sm-6 col-xl-4">
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
@endsection

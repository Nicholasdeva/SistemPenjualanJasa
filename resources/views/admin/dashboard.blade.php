@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Dashboard Admin</h1>
    <p class="text-secondary small mb-0">Ringkasan aktivitas marketplace.</p>
</div>

@php
    $rupiah = fn ($amount) => 'Rp' . number_format((float) $amount, 0, ',', '.');
    $cards = [
        ['Total pengguna', $stats['users'], 'bi-people', null],
        ['Total pembeli', $stats['buyers'], 'bi-person', null],
        ['Total penjual', $stats['sellers'], 'bi-person-workspace', route('admin.sellers.index')],
        ['Total admin', $stats['admins'], 'bi-shield-check', null],
        ['Penjual belum terverifikasi', $stats['sellers_pending'], 'bi-person-check', route('admin.sellers.index')],
        ['Total jasa', $stats['services'], 'bi-grid', route('admin.services.index')],
        ['Total pesanan', $stats['orders'], 'bi-bag', null],
        ['Dana di escrow', $rupiah($stats['escrow_total']), 'bi-shield-lock', null],
        ['Penarikan menunggu', $stats['withdrawals_pending'], 'bi-cash-coin', route('admin.withdrawals.index')],
        ['Pendapatan platform', $rupiah($stats['platform_revenue']), 'bi-graph-up-arrow', route('admin.cash-flow.index')],
        ['Total penarikan (semua status)', $rupiah($stats['withdrawals_total']), 'bi-wallet2', route('admin.cash-flow.index')],
        ['Penarikan disetujui', $rupiah($stats['withdrawals_paid']), 'bi-cash-stack', route('admin.cash-flow.index')],
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

<div class="card mt-4">
    <div class="card-header bg-white py-3">
        <h2 class="h6 fw-semibold mb-0">Pesanan berdasarkan status</h2>
    </div>
    <div class="card-body">
        @if ($stats['orders_by_status']->isEmpty())
            <p class="text-secondary small mb-0">Belum ada pesanan.</p>
        @else
            <div class="d-flex flex-wrap gap-2">
                @foreach ($stats['orders_by_status'] as $status => $count)
                    <span class="badge text-bg-light border text-dark px-3 py-2">
                        {{ str_replace('_', ' ', ucfirst($status)) }}: {{ $count }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

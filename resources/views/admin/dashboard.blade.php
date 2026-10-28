@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<h1 class="h4 fw-bold mb-4">Dashboard Admin</h1>

<div class="row g-3">
    @php
        $cards = [
            ['Total Pengguna', $stats['users'], 'bi-people', 'text-primary'],
            ['Penjual Belum Terverifikasi', $stats['sellers_pending'], 'bi-person-check', 'text-warning'],
            ['Total Jasa', $stats['services'], 'bi-grid', 'text-success'],
            ['Total Pesanan', $stats['orders'], 'bi-bag', 'text-info'],
            ['Dana Escrow Tertahan', 'Rp ' . number_format($stats['escrow_total'], 0, ',', '.'), 'bi-safe', 'text-danger'],
            ['Penarikan Menunggu', $stats['withdrawals_pending'], 'bi-cash-coin', 'text-secondary'],
        ];
    @endphp

    @foreach ($cards as [$label, $value, $icon, $color])
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <i class="bi {{ $icon }} fs-2 {{ $color }}"></i>
                    <div>
                        <div class="text-muted small">{{ $label }}</div>
                        <div class="fs-4 fw-bold">{{ $value }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
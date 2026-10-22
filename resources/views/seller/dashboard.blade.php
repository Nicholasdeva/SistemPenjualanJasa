@extends('layouts.app')

@section('content')
<h1 class="h3 mb-4">Dashboard Penjual</h1>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Pesanan Aktif</div>
            <div class="fs-3 fw-semibold">{{ $activeOrders }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Saldo Wallet</div>
            <div class="fs-3 fw-semibold">Rp {{ number_format($walletBalance, 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Saldo Escrow</div>
            <div class="fs-3 fw-semibold">Rp {{ number_format($escrowBalance, 0, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm"><div class="card-body">
            <div class="text-muted small">Rating Rata-rata</div>
            <div class="fs-3 fw-semibold">{{ $averageRating }}</div>
        </div></div>
    </div>
</div>
@endsection
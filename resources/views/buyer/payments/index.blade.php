@extends('layouts.app')

@section('title', 'Checkout & Bayar')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">Checkout &amp; Bayar</h1>
        <p class="text-muted mb-0">Pesanan yang menunggu pembayaran. Dana ditahan aman di escrow sampai Anda menyetujui hasil kerja.</p>
    </div>
    <div class="d-flex gap-2">
        <x-button :href="route('catalog')" variant="primary" icon="bi-search">
            Cari Jasa
        </x-button>
    </div>
</div>

@if ($orders->isEmpty())
    <x-card>
        <x-empty-state
            title="Tidak Ada Pesanan Menunggu Pembayaran"
            description="Semua pesanan Anda sudah dibayar. Pilih jasa baru di katalog untuk memulai proyek berikutnya."
            icon="bi-credit-card"
            actionText="Cari Jasa"
            :actionUrl="route('catalog')"
        />
    </x-card>
@else
    <x-card title="Menunggu Pembayaran" subtitle="Klik Bayar Sekarang untuk menyelesaikan pesanan" icon="bi-credit-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice &amp; Jasa</th>
                        <th>Penjual</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $order->servicePackage->service->title ?? 'Jasa' }}</div>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-dark border me-1">{{ $order->invoice_no }}</span>
                                    Paket: {{ $order->servicePackage->tier_name ?? '-' }}
                                </div>
                            </td>
                            <td class="small">{{ $order->servicePackage->service->seller->name ?? '-' }}</td>
                            <td class="fw-medium">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$order->status" /></td>
                            <td class="text-end">
                                {{-- Sementara simulasi bayar. Diganti popup Midtrans Snap di langkah berikutnya. --}}
                                <form action="{{ route('buyer.orders.simulate-pay', $order->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <x-button type="submit" variant="success" size="sm" icon="bi-cash-coin">
                                        Bayar Sekarang
                                    </x-button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
@endif
@endsection
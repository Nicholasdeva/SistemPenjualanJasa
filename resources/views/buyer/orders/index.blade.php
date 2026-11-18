@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">Pesanan Saya</h1>
        <p class="text-muted mb-0">Kelola dan pantau status pengerjaan seluruh pesanan jasa Anda di sini.</p>
    </div>
    <div class="d-flex gap-2">
        <x-button :href="route('catalog')" variant="primary" icon="bi-search">
            Cari Jasa
        </x-button>
    </div>
</div>

@php
    $pendingCount = $orders->getCollection()->where('status', 'pending_payment')->count();
    // Status yang sudah punya Workspace (setelah pembayaran sukses).
    $workspaceStatuses = ['paid', 'in_progress', 'delivered', 'revision', 'completed', 'disputed'];
@endphp

@if ($pendingCount > 0)
    <x-alert type="warning" title="Ada pesanan menunggu pembayaran">
        Selesaikan pembayaran agar penjual bisa mulai bekerja. Dana ditahan aman di escrow sampai Anda menyetujui hasil kerja.
    </x-alert>
@endif

<x-card title="Semua Pesanan" subtitle="Daftar riwayat dan pesanan aktif Anda" icon="bi-bag-check">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice &amp; Jasa</th>
                    <th>Penjual</th>
                    <th>Total Harga</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
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
                            @if ($order->status === 'pending_payment')
                                {{-- Sementara memakai simulasi bayar. Diganti popup Midtrans Snap di langkah berikutnya. --}}
                                <form action="{{ route('buyer.orders.simulate-pay', $order->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <x-button type="submit" variant="success" size="sm" icon="bi-cash-coin">
                                        Bayar Sekarang
                                    </x-button>
                                </form>
                            @elseif (in_array($order->status, $workspaceStatuses, true))
                                <x-button :href="route('workspace.show', $order->id)" variant="outline-primary" size="sm" icon="bi-chat-left-text">
                                    Workspace
                                </x-button>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Belum ada pesanan. <a href="{{ route('catalog') }}">Cari jasa</a> untuk memulai.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div class="mt-3">
            {{ $orders->links() }}
        </div>
    @endif
</x-card>
@endsection
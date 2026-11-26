@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1">Pesanan Saya</h1>
        <p class="text-secondary small mb-0">Kelola dan pantau status pengerjaan seluruh pesanan jasa Anda di sini.</p>
    </div>
    <div class="d-flex gap-2">
        <x-button :href="route('catalog')" variant="primary" size="sm" icon="bi-search">
            Cari Jasa
        </x-button>
    </div>
</div>

@if (($counts['pending_payment'] ?? 0) > 0 && $filter !== 'pending_payment')
    <x-alert type="warning" title="Ada pesanan menunggu pembayaran">
        Anda memiliki {{ $counts['pending_payment'] }} pesanan yang belum dibayar. Selesaikan pembayaran agar penjual bisa mulai bekerja.
        <a href="{{ route('buyer.orders.index', ['status' => 'pending_payment']) }}" class="alert-link">Lihat pesanan menunggu bayar</a>.
    </x-alert>
@endif

<ul class="nav nav-tabs mb-3">
    @foreach ($filters as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $filter === $key ? 'active' : '' }}"
               href="{{ route('buyer.orders.index', $key === 'all' ? [] : ['status' => $key]) }}">
                {{ $label }}
                <span class="badge {{ $key === 'pending_payment' && ($counts[$key] ?? 0) > 0 ? 'text-bg-warning' : 'text-bg-secondary' }} ms-1">
                    {{ $counts[$key] ?? 0 }}
                </span>
            </a>
        </li>
    @endforeach
</ul>

@if ($orders->isEmpty())
    <x-card>
        <x-empty-state
            :title="$filter === 'all' ? 'Belum Ada Pesanan' : 'Tidak Ada Pesanan di Tab Ini'"
            description="Pilih jasa terbaik di katalog untuk memulai proyek baru dengan perlindungan Escrow."
            icon="bi-bag-check"
            actionText="Cari Jasa"
            :actionUrl="route('catalog')"
        />
    </x-card>
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice &amp; Jasa</th>
                        <th>Penjual</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        @php
                            $isPending = in_array($order->status, ['pending_payment', 'pending'], true);
                            $hasWorkspace = in_array($order->status, \App\Models\Order::WORKSPACE_STATUSES, true);
                            $isOverdue = $order->isOverdue();
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $order->servicePackage->service->title ?? 'Jasa' }}</div>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-dark border me-1">{{ $order->invoice_no }}</span>
                                    Paket: {{ $order->servicePackage->tier_name ?? '-' }}
                                </div>
                            </td>
                            <td class="small">{{ $order->servicePackage->service->seller->name ?? '-' }}</td>
                            <td class="fw-medium text-nowrap">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$order->status" /></td>
                            <td class="small text-nowrap {{ $isOverdue ? 'text-danger fw-semibold' : 'text-secondary' }}">
                                @if ($order->deadline_at)
                                    {{ $order->deadline_at->format('d M Y, H:i') }}
                                    @if ($isOverdue)
                                        <div class="text-danger small">Terlambat</div>
                                    @endif
                                @else
                                    <span>Menunggu bayar</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex gap-1">
                                    @if ($isPending)
                                        <x-button :href="route('buyer.orders.pay', $order->id)" variant="success" size="sm" icon="bi-cash-coin">
                                            Bayar Sekarang
                                        </x-button>
                                    @elseif ($hasWorkspace)
                                        <x-button :href="route('workspace.show', $order->id)" variant="outline-primary" size="sm" icon="bi-chat-left-text">
                                            Workspace
                                        </x-button>
                                    @endif

                                    @if ($order->status === 'completed' && !$order->review)
                                        <x-button :href="route('buyer.reviews.create', $order->id)" variant="warning" size="sm" icon="bi-star-fill">
                                            Ulasan
                                        </x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($orders->hasPages())
            <div class="card-footer bg-white py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endif
@endsection
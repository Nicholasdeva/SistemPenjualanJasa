@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">Pesanan Saya</h1>
        <p class="text-muted mb-0">Daftar seluruh transaksi pembelian jasa, batas waktu pengerjaan, dan akses cepat ke Workspace.</p>
    </div>
    <div>
        <x-button :href="route('buyer.checkout')" variant="primary" icon="bi-plus-lg">
            Buat Pesanan Baru (Demo)
        </x-button>
    </div>
</div>

<!-- Filter Tab Status Pesanan -->
@php
    $tabs = [
        'all'             => ['label' => 'Semua Pesanan', 'count' => $allOrders->count()],
        'pending_payment' => ['label' => 'Menunggu Bayar', 'count' => $allOrders->where('status', 'pending_payment')->count()],
        'in_progress'     => ['label' => 'Sedang Dikerjakan', 'count' => $allOrders->where('status', 'in_progress')->count()],
        'delivered'       => ['label' => 'Hasil Dikirim', 'count' => $allOrders->where('status', 'delivered')->count()],
        'completed'       => ['label' => 'Selesai', 'count' => $allOrders->where('status', 'completed')->count()],
        'disputed'        => ['label' => 'Sengketa', 'count' => $allOrders->where('status', 'disputed')->count()],
    ];
@endphp

<ul class="nav nav-pills mb-3 gap-1 flex-nowrap overflow-auto pb-1">
    @foreach ($tabs as $key => $tab)
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 {{ $selectedStatus === $key ? 'active' : 'bg-white text-secondary border' }}"
               href="{{ route('buyer.orders.index', $key === 'all' ? [] : ['status' => $key]) }}">
                <span>{{ $tab['label'] }}</span>
                <span class="badge rounded-pill {{ $selectedStatus === $key ? 'bg-white text-primary' : 'text-bg-secondary' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        </li>
    @endforeach
</ul>

<x-card bodyClass="p-0">
    @if ($orders->isEmpty())
        <div class="p-4">
            <x-empty-state
                title="Tidak Ada Pesanan pada Status Ini"
                description="Belum ada pesanan yang cocok dengan filter status yang Anda pilih."
                icon="bi-Journal-x"
                actionText="Tampilkan Semua Pesanan"
                :actionUrl="route('buyer.orders.index')"
            />
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No. Invoice</th>
                        <th>Layanan &amp; Penjual</th>
                        <th>Paket</th>
                        <th>Total Biaya</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $order['invoice_no'] }}</div>
                                <div class="text-muted small">{{ $order['ordered_at'] }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $order['service_title'] }}</div>
                                <div class="text-muted small">
                                    <span class="badge bg-light text-dark border me-1">{{ $order['category'] }}</span>
                                    <i class="bi bi-person me-1"></i>{{ $order['seller_name'] }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">
                                    {{ $order['tier_name'] }}
                                </span>
                            </td>
                            <td class="fw-semibold">
                                Rp {{ number_format($order['total_price'], 0, ',', '.') }}
                            </td>
                            <td>
                                <x-status-badge :status="$order['status']" />
                            </td>
                            <td>
                                <div class="small fw-medium {{ $order['is_overdue'] ? 'text-danger' : 'text-dark' }}">
                                    {{ $order['deadline_at'] }}
                                </div>
                                <div class="small {{ $order['is_overdue'] ? 'text-danger fw-semibold' : 'text-muted' }}">
                                    {{ $order['days_left'] }}
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1 flex-wrap justify-content-end">
                                    @if ($order['status'] === 'pending_payment')
                                        <x-button :href="route('buyer.checkout', 1)" variant="success" size="sm" icon="bi-credit-card">
                                            Bayar
                                        </x-button>
                                    @else
                                        <x-button :href="route('workspace.show', $order['id'])" variant="primary" size="sm" icon="bi-chat-dots">
                                            Buka Workspace
                                        </x-button>
                                    @endif

                                    @if ($order['status'] === 'completed' && !$order['has_review'])
                                        <x-button :href="route('buyer.reviews.create', $order['id'])" variant="warning" size="sm" icon="bi-star-fill">
                                            Beri Ulasan
                                        </x-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-card>
@endsection

@extends('layouts.app')

@section('title', 'Pesanan Masuk')

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Pesanan Masuk</h1>
    <p class="text-secondary small mb-0">Pesanan dari pembeli untuk jasa kamu. Pesanan yang sudah dibayar menunggu kamu mulai kerjakan.</p>
</div>

<ul class="nav nav-tabs mb-3">
    @foreach ($filters as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $filter === $key ? 'active' : '' }}" href="{{ route('seller.orders.index', $key === 'all' ? [] : ['status' => $key]) }}">
                {{ $label }}
                <span class="badge {{ $key === 'paid' && $counts['paid'] > 0 ? 'text-bg-warning' : 'text-bg-secondary' }} ms-1">{{ $counts[$key] }}</span>
            </a>
        </li>
    @endforeach
</ul>

@if ($orders->isEmpty())
    <x-empty-state
        :title="$filter === 'all' ? 'Belum ada pesanan masuk' : 'Tidak ada pesanan di tab ini'"
        description="Pesanan baru akan muncul di sini setelah pembeli membayar."
        icon="bi-inbox"
    />
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice</th>
                        <th>Jasa &amp; pembeli</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        @php
                            $late = $order->deadline_at && $order->deadline_at->isPast() && in_array($order->status, ['paid', 'in_progress', 'revision']);
                        @endphp
                        <tr>
                            <td class="text-nowrap">
                                <div class="fw-semibold small">{{ $order->invoice_no }}</div>
                                <div class="small text-secondary">{{ $order->created_at->format('d M Y') }}</div>
                            </td>
                            <td>
                                <div>{{ $order->servicePackage->service->title }}</div>
                                <div class="small text-secondary">Paket {{ $order->servicePackage->tier_name }} · {{ $order->buyer->name ?? '-' }}</div>
                            </td>
                            <td class="text-nowrap">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$order->status" /></td>
                            <td class="text-nowrap {{ $late ? 'text-danger fw-semibold' : '' }}">
                                {{ $order->deadline_at ? $order->deadline_at->format('d M Y') : '-' }}
                                @if ($late)<div class="small">Terlambat</div>@endif
                            </td>
                            <td class="text-end text-nowrap">
                                @can('start', $order)
                                    <form action="{{ route('seller.orders.start', $order) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Mulai kerjakan pesanan {{ $order->invoice_no }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-primary">Mulai kerjakan</button>
                                    </form>
                                @endcan
                                <a href="{{ route('workspace.show', $order->id) }}" class="btn btn-sm btn-outline-primary">Workspace</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

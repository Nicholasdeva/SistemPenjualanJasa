@extends('layouts.app')

@section('title', 'Pesanan Masuk')

@section('content')
<h1 class="h3 mb-3">Pesanan Masuk</h1>

@if ($orders->isEmpty())
    <x-empty-state
        title="Belum Ada Pesanan Masuk"
        description="Saat ini belum ada pesanan baru dari pembeli untuk layanan Anda."
        icon="bi-inbox"
    />
@else
    <div class="table-responsive">
        <table class="table table-striped bg-white align-middle shadow-sm rounded overflow-hidden">
            <thead class="table-light">
                <tr>
                    <th>Invoice</th>
                    <th>Layanan</th>
                    <th>Paket</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                <tr>
                    <td class="fw-semibold">{{ $order->invoice_no }}</td>
                    <td>{{ $order->servicePackage->service->title }}</td>
                    <td>{{ $order->servicePackage->tier_name }}</td>
                    <td><x-status-badge :status="$order->status" /></td>
                    <td>{{ $order->deadline_at ? $order->deadline_at->format('d M Y') : '-' }}</td>
                    <td>
                        <a href="{{ route('workspace.show', $order->id) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-chat-dots me-1"></i>Buka Workspace
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection

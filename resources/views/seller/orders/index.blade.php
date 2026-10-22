@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Pesanan Masuk</h1>

@if ($orders->isEmpty())
    <div class="alert alert-secondary">Tidak ada pesanan.</div>
@else
    <div class="table-responsive">
        <table class="table table-striped bg-white align-middle">
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
                    <td>{{ $order->invoice_no }}</td>
                    <td>{{ $order->servicePackage->service->title }}</td>
                    <td>{{ $order->servicePackage->tier_name }}</td>
                    <td><span class="badge text-bg-info">{{ $order->status }}</span></td>
                    <td>{{ $order->deadline_at ? $order->deadline_at->format('d M Y') : '-' }}</td>
                    <td><a href="#" class="btn btn-sm btn-primary disabled">Buka Workspace</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
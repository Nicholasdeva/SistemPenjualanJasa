@extends('layouts.app')

@section('title', 'Dispute Center')

@php
    $statusLabel = [
        'open'                   => ['Terbuka', 'text-bg-danger'],
        'resolved_buyer_refund'  => ['Refund ke pembeli', 'text-bg-secondary'],
        'resolved_seller_payout' => ['Cair ke penjual', 'text-bg-secondary'],
    ];
@endphp

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Dispute Center</h1>
    <p class="text-secondary small mb-0">Sengketa antara pembeli dan penjual yang perlu diputuskan admin.</p>
</div>

@if ($disputes->isEmpty())
    <x-empty-state title="Tidak ada sengketa" description="Belum ada sengketa yang diajukan." icon="bi-shield-check" />
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Pesanan</th>
                        <th>Pelapor</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($disputes as $dispute)
                        @php [$label, $class] = $statusLabel[$dispute->status] ?? [ucfirst($dispute->status), 'text-bg-secondary']; @endphp
                        <tr>
                            <td class="mono small text-nowrap">{{ $dispute->order->invoice_no ?? '#' . $dispute->order_id }}</td>
                            <td>{{ $dispute->raiser->name ?? '-' }}</td>
                            <td class="small text-secondary">{{ \Illuminate\Support\Str::limit($dispute->reason, 70) }}</td>
                            <td><span class="badge {{ $class }}">{{ $label }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.disputes.show', $dispute) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

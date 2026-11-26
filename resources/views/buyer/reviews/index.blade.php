@extends('layouts.app')

@section('title', 'Form Ulasan Jasa')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">Ulasan Jasa</h1>
    <p class="text-muted mb-0">Beri penilaian untuk pesanan yang sudah selesai agar membantu pembeli lain.</p>
</div>

@if ($orders->isEmpty())
    <x-card>
        <x-empty-state
            title="Belum Ada Pesanan Selesai"
            description="Ulasan bisa diberikan setelah Anda menyetujui hasil kerja penjual."
            icon="bi-star"
            actionText="Lihat Pesanan Saya"
            :actionUrl="route('buyer.orders.index')"
        />
    </x-card>
@else
    <x-card title="Pesanan Selesai" subtitle="Beri ulasan atau lihat penilaian yang sudah Anda berikan" icon="bi-star-fill">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice &amp; Jasa</th>
                        <th>Penjual</th>
                        <th>Selesai</th>
                        <th class="text-end">Ulasan</th>
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
                            <td class="small">{{ $order->updated_at->format('d M Y') }}</td>
                            <td class="text-end">
                                @if ($order->review)
                                    <x-star-rating :rating="$order->review->rating" :showValue="true" />
                                @else
                                    <x-button :href="route('buyer.reviews.create', $order->id)" variant="warning" size="sm" icon="bi-star-fill">
                                        Beri Ulasan
                                    </x-button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
@endif
@endsection
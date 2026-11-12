@extends('layouts.app')

@section('title', 'Dashboard Pembeli')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h1 class="h3 fw-bold mb-1">Dashboard Pembeli</h1>
        <p class="text-muted mb-0">Selamat datang kembali, <strong>{{ auth()->user()->name ?? 'Pembeli' }}</strong>! Pantau pesanan aktif dan progres kerja penjual di sini.</p>
    </div>
    <div class="d-flex gap-2">
        <x-button :href="route('buyer.orders.index')" variant="outline-primary" icon="bi-bag-check">
            Semua Pesanan
        </x-button>
        <x-button :href="route('buyer.checkout')" variant="primary" icon="bi-cart-plus">
            Simulasi Checkout
        </x-button>
    </div>
</div>

<!-- Kartu Ringkasan Pembeli -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-card class="h-100 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium">Pesanan Aktif</div>
                    <div class="fs-2 fw-bold text-primary mt-1">{{ $stats['active_orders'] }}</div>
                    <div class="small text-muted">Sedang dikerjakan penjual</div>
                </div>
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-3">
                    <i class="bi bi-hourglass-split fs-3"></i>
                </div>
            </div>
        </x-card>
    </div>

    <div class="col-sm-6 col-xl-3">
        <x-card class="h-100 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium">Menunggu Persetujuan</div>
                    <div class="fs-2 fw-bold text-warning mt-1">{{ $stats['waiting_approval'] }}</div>
                    <div class="small text-muted">Hasil siap direview</div>
                </div>
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning p-3">
                    <i class="bi bi-box-seam fs-3"></i>
                </div>
            </div>
        </x-card>
    </div>

    <div class="col-sm-6 col-xl-3">
        <x-card class="h-100 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium">Pesanan Selesai</div>
                    <div class="fs-2 fw-bold text-success mt-1">{{ $stats['completed_orders'] }}</div>
                    <div class="small text-muted">Proyek tuntas 100%</div>
                </div>
                <div class="rounded-3 bg-success bg-opacity-10 text-success p-3">
                    <i class="bi bi-check2-all fs-3"></i>
                </div>
            </div>
        </x-card>
    </div>

    <div class="col-sm-6 col-xl-3">
        <x-card class="h-100 border-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small fw-medium">Dana di Escrow</div>
                    <div class="fs-4 fw-bold text-dark mt-1">Rp {{ number_format($stats['escrow_protected'], 0, ',', '.') }}</div>
                    <div class="small text-success"><i class="bi bi-shield-check me-1"></i>Terlindungi penuh</div>
                </div>
                <div class="rounded-3 bg-info bg-opacity-10 text-info p-3">
                    <i class="bi bi-shield-lock fs-3"></i>
                </div>
            </div>
        </x-card>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Pesanan Aktif & Riwayat Singkat -->
    <div class="col-lg-8">
        <!-- Pesanan Aktif -->
        <x-card title="Pesanan Aktif Saat Ini" subtitle="Klik Buka Workspace untuk berdiskusi atau mengunduh hasil kerja penjual" icon="bi-lightning-charge-fill" class="mb-4">
            <x-slot:headerAction>
                <a href="{{ route('buyer.orders.index') }}" class="btn btn-sm btn-link text-decoration-none">Lihat Semua <i class="bi bi-arrow-right"></i></a>
            </x-slot:headerAction>

            @if (empty($activeOrders))
                <x-empty-state
                    title="Belum Ada Pesanan Aktif"
                    description="Anda belum memiliki pesanan yang sedang berjalan. Pilih jasa terbaik di katalog untuk memulai proyek."
                    icon="bi-bag-plus"
                    actionText="Lihat Katalog Jasa"
                    :actionUrl="url('/')"
                />
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach ($activeOrders as $order)
                        <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
                                <div>
                                    <span class="badge text-bg-light border text-secondary me-1">{{ $order['invoice_no'] }}</span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">Paket {{ $order['tier_name'] }}</span>
                                </div>
                                <x-status-badge :status="$order['status']" />
                            </div>

                            <h6 class="fw-bold mb-1">{{ $order['service'] }}</h6>
                            <div class="small text-muted mb-2">
                                <i class="bi bi-person-circle me-1"></i>Penjual: <strong>{{ $order['seller_name'] }}</strong>
                                <span class="mx-2">•</span>
                                <i class="bi bi-calendar-event me-1"></i>Deadline: <strong class="text-dark">{{ $order['deadline'] }}</strong>
                            </div>
                            <div class="small text-secondary bg-white p-2 rounded border mb-3">
                                <i class="bi bi-info-circle text-primary me-1"></i>{{ $order['progress_note'] }}
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-semibold text-dark">
                                    Total: Rp {{ number_format($order['total_price'], 0, ',', '.') }}
                                </div>
                                <div class="d-flex gap-2">
                                    <x-button :href="route('workspace.show', $order['id'])" variant="primary" size="sm" icon="bi-chat-left-text">
                                        Buka Workspace
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <!-- Riwayat Singkat Pesanan -->
        <x-card title="Riwayat Pesanan Selesai" subtitle="Pesanan terakhir yang telah tuntas dikerjakan" icon="bi-clock-history">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice &amp; Jasa</th>
                            <th>Penjual</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Ulasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentHistory as $hist)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $hist['service'] }}</div>
                                    <div class="text-muted small">{{ $hist['invoice_no'] }} • Selesai {{ $hist['completed_at'] }}</div>
                                </td>
                                <td class="small">{{ $hist['seller_name'] }}</td>
                                <td class="fw-medium">Rp {{ number_format($hist['total_price'], 0, ',', '.') }}</td>
                                <td><x-status-badge :status="$hist['status']" /></td>
                                <td class="text-end">
                                    @if ($hist['has_review'])
                                        <x-star-rating :rating="$hist['rating']" :showValue="true" />
                                    @else
                                        <x-button :href="route('buyer.reviews.create', $hist['id'])" variant="warning" size="sm" icon="bi-star-fill">
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
    </div>

    <!-- Kolom Kanan: Jasa yang Disimpan (Wishlist) -->
    <div class="col-lg-4">
        <x-card title="Jasa yang Disimpan" subtitle="Daftar layanan favorit pilihan Anda" icon="bi-bookmark-heart-fill">
            <div class="d-flex flex-column gap-3">
                @foreach ($savedServices as $srv)
                    <div class="p-3 rounded-3 border bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $srv['category'] }}</span>
                            <x-star-rating :rating="$srv['rating']" :showValue="true" :count="$srv['reviews_count']" />
                        </div>
                        <h6 class="fw-semibold mb-1">{{ $srv['title'] }}</h6>
                        <div class="small text-muted mb-3">
                            Oleh <strong>{{ $srv['seller'] }}</strong> • Estimasi {{ $srv['delivery_days'] }} hari
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-2">
                            <div>
                                <span class="text-muted small d-block" style="font-size: 0.72rem;">Mulai dari</span>
                                <span class="fw-bold text-primary">Rp {{ number_format($srv['starting_price'], 0, ',', '.') }}</span>
                            </div>
                            <x-button :href="route('buyer.checkout', $srv['id'])" variant="outline-primary" size="sm" icon="bi-bag-plus">
                                Pesan Jasa
                            </x-button>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>
@endsection

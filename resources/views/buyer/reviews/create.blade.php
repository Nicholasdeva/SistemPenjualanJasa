@extends('layouts.app')

@section('title', 'Beri Ulasan Pesanan')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('buyer.orders.index') }}" class="text-decoration-none">Pesanan Saya</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Beri Ulasan</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-1">Beri Ulasan Layanan</h1>
            <p class="text-muted mb-0">Bagikan pengalaman kerja sama Anda untuk membantu pembeli lain dan mengapresiasi penjual.</p>
        </div>

        @if ($errors->any())
            <x-alert type="danger" title="Gagal mengirim ulasan:">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <!-- Ringkasan Pesanan Selesai -->
        <x-card class="mb-4 bg-light border-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge text-bg-dark">{{ $orderSummary['invoice_no'] }}</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary">Paket {{ $orderSummary['tier_name'] }}</span>
                        <x-status-badge status="completed" />
                    </div>
                    <h5 class="fw-bold mb-1">{{ $orderSummary['service_title'] }}</h5>
                    <div class="small text-muted">
                        Penjual: <strong class="text-dark">{{ $orderSummary['seller_name'] }}</strong> • Diselesaikan pada {{ $orderSummary['completed_at'] }}
                    </div>
                </div>
                <div class="text-md-end">
                    <span class="text-muted small d-block">Total Nilai Proyek</span>
                    <span class="fs-5 fw-bold text-dark">Rp {{ number_format($orderSummary['total_price'], 0, ',', '.') }}</span>
                </div>
            </div>
        </x-card>

        <!-- Form Input Bintang 1-5 & Komentar -->
        <x-card title="Penilaian &amp; Komentar Anda" icon="bi-star-half">
            <form action="{{ route('buyer.reviews.store', $orderSummary['id']) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-semibold d-block">
                        Rating Kepuasan (1 sampai 5 Bintang) <span class="text-danger">*</span>
                    </label>
                    <x-star-rating
                        :interactive="true"
                        name="rating"
                        :rating="old('rating', 5)"
                    />
                </div>

                <div class="mb-4">
                    <label for="comment" class="form-label fw-semibold">
                        Komentar Ulasan <span class="text-danger">*</span>
                    </label>
                    <textarea
                        name="comment"
                        id="comment"
                        rows="5"
                        class="form-control @error('comment') is-invalid @enderror"
                        placeholder="Ceritakan kualitas hasil kerja, kecepatan respons, dan ketepatan waktu penjual..."
                        required
                    >{{ old('comment', 'Hasil desain logo sangat memuaskan, komunikasi penjual ramah, dan revisi dikerjakan tepat sesuai brief. Sangat direkomendasikan!') }}</textarea>
                    @error('comment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Satu pesanan yang selesai (<code>completed</code>) hanya dapat diberikan satu ulasan resmi.
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                    <x-button :href="route('buyer.orders.index')" variant="outline-secondary">
                        Batal
                    </x-button>
                    <x-button type="submit" variant="primary" icon="bi-send-check-fill">
                        Kirim Ulasan
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>
@endsection

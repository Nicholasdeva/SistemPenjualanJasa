@extends('layouts.app')

@section('title', 'Checkout & Pembayaran')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-1">
            <li class="breadcrumb-item"><a href="{{ route('buyer.dashboard') }}" class="text-decoration-none">Dashboard Pembeli</a></li>
            <li class="breadcrumb-item active" aria-current="page">Checkout &amp; Pembayaran Escrow</li>
        </ol>
    </nav>
    <h1 class="h3 fw-bold mb-1">Checkout &amp; Pembayaran</h1>
    <p class="text-muted mb-0">Lengkapi brief kebutuhan proyek Anda sebelum melakukan pembayaran ke rekening bersama (Escrow).</p>
</div>

@if ($errors->any())
    <x-alert type="danger" title="Mohon periksa kembali isian form Anda:">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

<form action="{{ route('buyer.checkout.store') }}" method="POST">
    @csrf
    <input type="hidden" name="service_package_id" value="{{ $selectedPackage['id'] }}">

    <div class="row g-4">
        <!-- Kolom Kiri: Pilihan Paket & Form Brief Proyek -->
        <div class="col-lg-7">
            <!-- Pilihan Paket -->
            <x-card title="1. Pilih Paket Layanan" subtitle="Klik salah satu paket untuk menyesuaikan cakupan kerja dan harga" icon="bi-layers-fill" class="mb-4">
                <div class="row g-2">
                    @foreach ($packages as $pkgId => $pkg)
                        @php $isSelected = $pkg['id'] === $selectedPackage['id']; @endphp
                        <div class="col-md-4">
                            <a href="{{ route('buyer.checkout', $pkgId) }}"
                               class="text-decoration-none d-block p-3 rounded-3 border h-100 {{ $isSelected ? 'border-primary bg-primary bg-opacity-10 shadow-sm' : 'bg-white text-dark' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold {{ $isSelected ? 'text-primary' : 'text-dark' }}">{{ $pkg['tier_name'] }}</span>
                                    @if ($isSelected)
                                        <i class="bi bi-check-circle-fill text-primary"></i>
                                    @endif
                                </div>
                                <div class="fs-6 fw-bold text-dark mb-1">
                                    Rp {{ number_format($pkg['price'], 0, ',', '.') }}
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-clock me-1"></i>{{ $pkg['delivery_days'] }} hari • {{ $pkg['revision_limit'] }}x revisi
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </x-card>

            <!-- Form Brief Proyek -->
            <x-card title="2. Brief &amp; Instruksi Proyek" subtitle="Jelaskan spesifikasi proyek sedetail mungkin agar hasil sesuai ekspektasi" icon="bi-file-earmark-text-fill">
                <div class="mb-3">
                    <label for="project_brief" class="form-label fw-semibold">
                        Deskripsi Kebutuhan / Brief Proyek <span class="text-danger">*</span>
                    </label>
                    <textarea
                        name="project_brief"
                        id="project_brief"
                        rows="6"
                        class="form-control @error('project_brief') is-invalid @enderror"
                        placeholder="Contoh: Saya membutuhkan desain logo untuk brand kedai kopi 'Kopi Senja' dengan nuansa hangat (cokelat & krem), gaya minimalis modern, serta target pasar anak muda..."
                        required
                    >{{ old('project_brief', 'Logo untuk toko kopi bernama "Kopi Senja", nuansa hangat (cokelat kopi dan krem keemasan), gaya minimalis modern untuk kemasan cup dan Instagram.') }}</textarea>
                    @error('project_brief')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        Minimal 20 karakter. Sertakan nama brand, preferensi warna, gaya visual, dan target audiens.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="reference_link" class="form-label fw-semibold">
                        Tautan Referensi / Moodboard (Opsional)
                    </label>
                    <input
                        type="url"
                        name="reference_link"
                        id="reference_link"
                        class="form-control"
                        placeholder="https://drive.google.com/..."
                        value="{{ old('reference_link') }}"
                    >
                </div>

                <div class="p-3 rounded-3 bg-light border small text-secondary">
                    <div class="fw-semibold text-dark mb-1">
                        <i class="bi bi-lightbulb text-warning me-1"></i>Tips Brief yang Efektif:
                    </div>
                    <ul class="mb-0 ps-3">
                        <li>Sebutkan ukuran/format file akhir yang Anda butuhkan.</li>
                        <li>Deadline pengerjaan ({{ $selectedPackage['delivery_days'] }} hari) dihitung sejak pembayaran terverifikasi.</li>
                        <li>Anda masih bisa berdiskusi dan mengirim file tambahan di <strong>Workspace</strong> setelah pembayaran.</li>
                    </ul>
                </div>
            </x-card>
        </div>

        <!-- Kolom Kanan: Ringkasan Paket & Rincian Biaya -->
        <div class="col-lg-5">
            <x-card title="Ringkasan Pesanan &amp; Biaya" icon="bi-receipt-cutoff" class="sticky-top" style="top: 80px;">
                <!-- Info Jasa & Penjual -->
                <div class="pb-3 mb-3 border-bottom">
                    <span class="badge bg-primary bg-opacity-10 text-primary mb-2">{{ $service['category'] }}</span>
                    <h5 class="fw-bold mb-1">{{ $service['title'] }}</h5>
                    <div class="d-flex align-items-center gap-2 small text-muted">
                        <span>Oleh <strong class="text-dark">{{ $service['seller_name'] }}</strong></span>
                        @if ($service['is_verified'])
                            <span class="badge text-bg-success"><i class="bi bi-patch-check-fill me-1"></i>Terverifikasi</span>
                        @endif
                        <x-star-rating :rating="$service['rating']" :showValue="true" :count="$service['reviews']" />
                    </div>
                </div>

                <!-- Detail Paket Terpilih -->
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold text-dark">Paket {{ $selectedPackage['tier_name'] }}</span>
                        <span class="badge text-bg-dark">{{ $selectedPackage['delivery_days'] }} Hari Kerja</span>
                    </div>
                    <ul class="list-unstyled small text-secondary mb-0">
                        @foreach ($selectedPackage['features'] as $feat)
                            <li class="mb-1 d-flex align-items-start gap-2">
                                <i class="bi bi-check2-circle text-success mt-1"></i>
                                <span>{{ $feat }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Rincian Biaya -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Harga Paket ({{ $selectedPackage['tier_name'] }})</span>
                        <span class="fw-medium">Rp {{ number_format($selectedPackage['price'], 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-2">
                        <span class="text-muted">Biaya Layanan Platform (10%)</span>
                        <span class="fw-medium">Rp {{ number_format($platformFee, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-3">
                        <span class="text-muted">Kuota Revisi Maksimal</span>
                        <span class="fw-medium">{{ $selectedPackage['revision_limit'] }}x Revisi</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <span class="fw-bold text-dark">Total Pembayaran</span>
                        <span class="fs-4 fw-bold text-primary">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Jaminan Escrow -->
                <div class="alert alert-info small py-2 px-3 mb-3">
                    <div class="fw-semibold"><i class="bi bi-shield-fill-check me-1"></i>Perlindungan Escrow Aktif</div>
                    Dana sebesar <strong>Rp {{ number_format($totalPrice, 0, ',', '.') }}</strong> disimpan di rekening bersama platform dan baru diteruskan ke penjual setelah Anda menyetujui hasil kerja.
                </div>

                <x-button type="submit" variant="primary" size="lg" icon="bi-lock-fill" class="w-100 fw-semibold">
                    Bayar Sekarang via Escrow
                </x-button>

                <div class="text-center mt-2 small text-muted">
                    <i class="bi bi-credit-card-2-front me-1"></i>Mendukung Midtrans Snap Sandbox (QRIS, Virtual Account, E-Wallet)
                </div>
            </x-card>
        </div>
    </div>
</form>
@endsection

@extends('layouts.app')

@section('title', 'Beranda')
@section('hide_sidebar', true)
@section('full_width', true)

@use('App\Support\CategoryStyle')

@php
    $user = auth()->user();
    $preview = $services->take(2);
    $sellerInitials = $services->pluck('seller.name')->filter()->unique()->take(4);

    $catItems = $categories->isNotEmpty()
        ? $categories->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug, 'count' => $c->services_count])
        : collect(['Desain', 'Pemrograman', 'Penulisan', 'Penerjemahan', 'Video', 'Pemasaran Digital'])
            ->map(fn ($n) => ['name' => $n, 'slug' => null, 'count' => null]);
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    /* ---------- Tipografi (khusus isi beranda, navbar tidak diubah) ---------- */
    main { --bs-body-font-family: 'Open Sans', 'Segoe UI', Arial, sans-serif; font-family: var(--bs-body-font-family); }
    main h1, main h2, main h3, main .sec-title, main .stats-bar .num, main .step-num { font-family: inherit; letter-spacing: 0; }
    main .stats-bar .num { font-variant-numeric: tabular-nums; font-weight: 700; }
    .arr { font-family: system-ui, sans-serif; }

    /* ---------- Hero ---------- */
    .hero {
        position: relative;
        background: #fff;
        border-bottom: 1px solid #dfe5ee;
        overflow: hidden;
    }
    .hero::before { /* grid tipis di latar, seperti contoh */
        content: ''; position: absolute; inset: 0; pointer-events: none;
        background-image:
            linear-gradient(#eef1f6 1px, transparent 1px),
            linear-gradient(90deg, #eef1f6 1px, transparent 1px);
        background-size: 72px 72px;
        -webkit-mask-image: radial-gradient(ellipse at 70% 40%, #000 0%, transparent 70%);
        mask-image: radial-gradient(ellipse at 70% 40%, #000 0%, transparent 70%);
    }
    .hero h1 {
        font-size: clamp(2.2rem, 5vw, 3.9rem);
        font-weight: 700;
        line-height: 1.05;
        letter-spacing: -.03em;
        color: #0b0f19;
    }
    .hero-lead { font-size: 1rem; color: #5a6880; max-width: 480px; line-height: 1.65; }

    .hero-badge {
        display: inline-flex; align-items: center; gap: .6rem;
        padding: .3rem .9rem .3rem .3rem;
        background: #f1f3f7; border-radius: 999px; font-size: .78rem;
    }
    .hero-badge .tag { background: #fff; padding: .3rem .8rem; border-radius: 999px; font-weight: 600; }
    .hero-badge .txt { font-weight: 600; color: #334155; }

    .btn-pill { border-radius: 999px; padding: .7rem 1.8rem; font-weight: 600; }

    .hero-art { animation: hero-float 5s ease-in-out infinite; }
    .hero-img {
        width: 100%; height: auto; position: relative;
        mix-blend-mode: multiply; /* latar putih gambar melebur ke hero */
        -webkit-mask-image: radial-gradient(closest-side, #000 62%, transparent 100%);
        mask-image: radial-gradient(closest-side, #000 62%, transparent 100%);
        transform-origin: 33% 52%;
        transform: translateX(17%) scale(1.75); /* perbesar toolbox, buang area kosong gambar */
    }
    @media (max-width: 991.98px) { .hero-img { transform: scale(1.3); transform-origin: 33% 52%; margin: 1rem 0; } }
    @keyframes hero-float { 50% { transform: translateY(-10px); } }
    @media (prefers-reduced-motion: reduce) { .hero-art { animation: none; } }

    .hero-stats { position: relative; }
    .hero-stats .stat { padding: .25rem 1.25rem; }
    .hero-stats .stat + .stat { border-left: 1px solid #dfe5ee; }
    .hero-stats .num { font-size: clamp(1.4rem, 3vw, 2rem); font-weight: 700; color: #0b0f19; font-variant-numeric: tabular-nums; }
    .hero-stats .lbl { font-size: .9rem; color: #5a6880; }

    /* ---------- Section ---------- */
    .sec-title { font-size: clamp(1.35rem, 2.6vw, 1.7rem); font-weight: 600; margin-bottom: .35rem; }
    .sec-sub { color: #5a6880; margin: 0; }

    .cat-tile {
        display: flex; align-items: center; gap: .85rem;
        padding: 1rem 1.1rem;
        background: #fff; border: 1px solid #dfe5ee; border-radius: 1rem; box-shadow: 0 1px 3px rgba(15, 23, 42, .07);
        text-decoration: none; color: #0f172a;
        transition: all .18s ease;
    }
    .cat-tile:hover { border-color: #2563eb; color: #0f172a; }
    .cat-tile .arrow { margin-left: auto; color: #94a3b8; transition: all .18s; }
    .cat-tile:hover .arrow { color: #2563eb; transform: translateX(2px); }

    /* langkah */
    .steps { position: relative; }
    .steps::before {
        content: ''; position: absolute; top: 22px; left: 12%; right: 12%;
        border-top: 1px solid #dfe5ee;
    }
    .step-num {
        width: 44px; height: 44px; border-radius: 50%;
        background: #fff; border: 1px solid #cbd5e3; color: #2563eb;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 600; position: relative; z-index: 1;
        box-shadow: 0 0 0 6px #f8fafd;
    }
    @media (max-width: 991.98px) { .steps::before { display: none; } }

    .role-card { border-radius: 1.25rem; }
    .check-list li { display: flex; gap: .6rem; margin-bottom: .7rem; color: #334155; }
    .check-list li::before {
        content: ''; flex: 0 0 auto; width: 6px; height: 6px; margin-top: .55rem;
        border-radius: 1px; background: #2563eb;
    }

    .cta-band { background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 55%, #3b82f6 100%); }
    .cta-old {
        border-radius: 1.5rem;
        background: #fff;
        border: 1px solid #dfe5ee;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .07);
    }

</style>
@endpush

@section('content')

{{-- ================= HERO ================= --}}
<section class="hero pt-5 pb-4 pt-lg-5 pb-lg-4">
<div class="container position-relative">
    <div class="row g-4 align-items-center">
        <div class="col-lg-6 position-relative" style="z-index: 1;">
            <div class="hero-badge mb-4">
                <span class="tag">Pengguna kami</span>
                <span class="txt">Dipercaya {{ number_format(max($stats['sellers'], 1) * 10, 0, ',', '.') }}+ orang</span>
            </div>

            <h1 class="mb-4">Jasa Digital Jadi Lebih Mudah dan Aman</h1>

            <p class="hero-lead mb-4">
                Pesan jasa desain, penulisan, sampai website dengan harga paket yang jelas.
                Dana ditahan di escrow dan baru diteruskan setelah hasilnya kamu setujui.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('catalog') }}" class="btn btn-primary btn-pill">Jelajahi Jasa</a>
                @guest
                    <a href="{{ route('register', ['role' => 'seller']) }}" class="btn btn-outline-secondary btn-pill">Mulai Jualan</a>
                @endguest
            </div>
        </div>

        <div class="col-lg-6 text-center">
            <div class="hero-art"><img src="{{ asset('images/hero-toolbox.png') }}" alt="Kotak peralatan jasa digital" class="hero-img"></div>
        </div>
    </div>

    <div class="row g-0 hero-stats mt-4 pt-4">
        <div class="col-4 stat">
            <div class="num">{{ number_format($stats['services'], 0, ',', '.') }}</div>
            <div class="lbl">Jasa Aktif</div>
        </div>
        <div class="col-4 stat">
            <div class="num">{{ number_format($stats['sellers'], 0, ',', '.') }}</div>
            <div class="lbl">Penjual Terverifikasi</div>
        </div>
        <div class="col-4 stat">
            <div class="num">{{ number_format($stats['completed'], 0, ',', '.') }}</div>
            <div class="lbl">Pesanan Selesai</div>
        </div>
    </div>
</div>
</section>

<div class="container pt-5 pb-5">

{{-- ================= CARA KERJA ================= --}}
<section id="cara-kerja" class="mb-5">
    <div class="text-center mb-5">
        <h2 class="sec-title">Cara kerjanya</h2>
        <p class="sec-sub">Empat langkah, dan uangmu aman di setiap tahapnya.</p>
    </div>

    <div class="row g-4 steps text-center">
        @foreach ([
            ['Pilih paket', 'Bandingkan harga, lama pengerjaan, dan jumlah revisi di setiap paket.'],
            ['Bayar ke escrow', 'Dana ditahan sistem, belum masuk ke penjual sampai pekerjaan beres.'],
            ['Kerja di workspace', 'Kirim brief, terima hasil, dan minta revisi langsung di satu tempat.'],
            ['Setujui hasil', 'Dana diteruskan ke penjual, lalu kamu bisa memberi ulasan.'],
        ] as $i => [$title, $text])
            <div class="col-md-6 col-lg-3">
                <span class="step-num mb-3">{{ $i + 1 }}</span>
                <h3 class="h6 fw-bold mb-2">{{ $title }}</h3>
                <p class="small text-secondary mb-0 mx-auto" style="max-width: 240px;">{{ $text }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ================= PEMBELI & PENJUAL ================= --}}
<section class="row g-4 mb-5">
    <div class="col-lg-6">
        <div class="card role-card h-100">
            <div class="card-body p-4 p-lg-5">
                <h3 class="h4 fw-bold mb-2">Untuk pembeli</h3>
                <p class="text-secondary mb-4">Temukan orang yang tepat tanpa khawatir uang hilang.</p>
                <ul class="list-unstyled check-list mb-4">
                    <li>Dana aman di escrow sampai kamu setuju</li>
                    <li>Harga dan jatah revisi jelas sebelum bayar</li>
                    <li>Admin membantu kalau terjadi sengketa</li>
                </ul>
                @guest
                    <a href="{{ route('register', ['role' => 'buyer']) }}" class="btn btn-primary">Daftar sebagai pembeli</a>
                @else
                    <a href="{{ route('catalog') }}" class="btn btn-outline-primary">Cari jasa</a>
                @endguest
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card role-card h-100">
            <div class="card-body p-4 p-lg-5">
                <h3 class="h4 fw-bold mb-2">Untuk penjual</h3>
                <p class="text-secondary mb-4">Tawarkan keahlianmu dan terima pembayaran yang pasti.</p>
                <ul class="list-unstyled check-list mb-4">
                    <li>Pembeli sudah bayar di depan sebelum kamu mulai</li>
                    <li>Atur sendiri paket, harga, dan batas revisi</li>
                    <li>Tarik saldo langsung ke rekening bank</li>
                </ul>
                @guest
                    <a href="{{ route('register', ['role' => 'seller']) }}" class="btn btn-outline-primary">Daftar sebagai penjual</a>
                @else
                    @if ($user->role === 'seller')
                        <a href="{{ route('seller.services.create') }}" class="btn btn-outline-primary">Tambah jasa baru</a>
                    @endif
                @endguest
            </div>
        </div>
    </div>
</section>

{{-- ================= FAQ ================= --}}
<section id="faq" class="row g-4 mb-5 pt-2">
    <div class="col-lg-4">
        <h2 class="sec-title">Pertanyaan umum</h2>
        <p class="sec-sub">Hal yang sering ditanyakan pengguna baru.</p>
    </div>
    <div class="col-lg-8">
        <div class="accordion" id="faqAccordion">
            @foreach ([
                ['Apa itu sistem escrow?', 'Escrow adalah penampungan dana sementara. Pembayaran dari pembeli disimpan selama pesanan berjalan dan baru diteruskan ke penjual setelah pembeli menyetujui hasil kerja.'],
                ['Berapa kali saya bisa meminta revisi?', 'Sesuai jumlah revisi pada paket yang dipilih. Informasinya tertera sebelum pembayaran.'],
                ['Bagaimana jika penjual tidak mengirimkan hasil?', 'Ajukan sengketa melalui workspace pesanan. Admin akan meninjau dan dana dapat dikembalikan kepada pembeli.'],
                ['Apakah mendaftar sebagai penjual dikenakan biaya?', 'Tidak. Daftar, lengkapi profil dan data rekening, kemudian tunggu verifikasi dari admin.'],
            ] as $i => [$q, $a])
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button {{ $i ? 'collapsed' : '' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#faq{{ $i }}">
                            {{ $q }}
                        </button>
                    </h3>
                    <div id="faq{{ $i }}" class="accordion-collapse collapse {{ $i ? '' : 'show' }}" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-secondary">{{ $a }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

</div>

{{-- ================= CTA ================= --}}
<section class="cta-band py-5">
    <div class="container py-lg-3">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <h2 class="text-white fw-bold mb-2">Siap mulai proyek pertamamu?</h2>
                <p class="mb-0" style="color:#dbe7ff;">Daftar gratis, temukan penjual yang cocok, dan bayar dengan aman.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-pill btn-lg text-primary">Buka dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-light btn-pill btn-lg text-primary">Daftar gratis</a>
                @endauth
            </div>
        </div>
    </div>
</section>

@endsection

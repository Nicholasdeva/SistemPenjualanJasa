@extends('layouts.app')

@section('title', 'Beranda')
@section('hide_sidebar', true)

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
<style>
    /* ---------- Hero ---------- */
    .hero {
        position: relative;
        border-radius: 1.5rem;
        background: #fff;
        border: 1px solid #dfe5ee;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .07);
    }
    .hero h1 {
        font-size: clamp(1.75rem, 3.4vw, 2.5rem);
        font-weight: 700;
        line-height: 1.15;
        letter-spacing: -.01em;
    }
    .hero h1 .hl { color: #2563eb; }
    .hero-lead { font-size: 1.05rem; color: #5a6880; max-width: 520px; line-height: 1.65; }

    .hero-search {
        background: #fff;
        border: 1px solid #cbd5e3;
        border-radius: 1rem;
        padding: .4rem;
        box-shadow: 0 10px 28px -14px rgba(15, 23, 42, .25);
        max-width: 560px;
    }
    .hero-search .form-control { border: 0; box-shadow: none; font-size: .98rem; padding-left: .5rem; }
    .hero-search .btn { border-radius: .75rem; padding: .7rem 1.4rem; }

    .chip {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .35rem .8rem;
        font-size: .82rem; font-weight: 600;
        color: #334155; background: #fff;
        border: 1px solid #cbd5e3; border-radius: 999px;
        text-decoration: none;
        transition: all .15s;
    }
    .chip i { color: #2563eb; }
    .chip:hover { border-color: #2563eb; color: #2563eb; }

    .avatar-stack span {
        width: 32px; height: 32px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: .75rem; font-weight: 700;
        border: 2px solid #fff;
        margin-left: -8px;
    }
    .avatar-stack span:first-child { margin-left: 0; }

    /* preview kanan */
    .preview { position: relative; max-width: 400px; margin-left: auto; }
    .preview .card { box-shadow: 0 24px 48px -24px rgba(15, 23, 42, .35); }
    .preview .p-2nd { margin: -1.25rem 0 0 2.5rem; }
    .float-chip {
        position: absolute; z-index: 3;
        background: #fff; border: 1px solid #cbd5e3; border-radius: .85rem;
        padding: .55rem .8rem; font-size: .8rem; font-weight: 600; color: #0f172a;
        box-shadow: 0 12px 28px -12px rgba(15, 23, 42, .3);
        display: flex; align-items: center; gap: .5rem;
    }
    .float-chip.top { top: -14px; right: -10px; }
    .float-chip.bottom { bottom: -16px; left: -18px; }

    /* ---------- Stats ---------- */
    .stats-bar .stat + .stat { border-left: 1px solid #dfe5ee; }
    .stats-bar .num { font-size: 1.6rem; font-weight: 600; color: #0f172a; }

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
    .check-list i { color: #2563eb; margin-top: .1rem; }

    .cta {
        border-radius: 1.5rem;
        background: #fff;
        border: 1px solid #dfe5ee;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .07);
    }

    @media (max-width: 767.98px) {
        .stats-bar .stat:nth-child(3) { border-left: 0; }
        .stats-bar .stat:nth-child(n+3) { border-top: 1px solid #dfe5ee; }
    }
</style>
@endpush

@section('content')

{{-- ================= HERO ================= --}}
<section class="hero px-4 py-5 p-lg-5 mb-4">
    <div class="row g-5 align-items-center py-lg-3">
        <div class="col-lg-6">
            <h1 class="mb-3">Kerjakan proyekmu bareng <span class="hl">freelancer terverifikasi</span></h1>
            <p class="hero-lead mb-4">
                Pesan jasa desain, penulisan, sampai website dengan harga paket yang jelas.
                Dana kamu ditahan di escrow dan baru diteruskan setelah hasilnya kamu setujui.
            </p>

            <form action="{{ route('catalog') }}" method="GET" class="hero-search d-flex align-items-center gap-1 mb-3">
                <i class="bi bi-search text-primary ms-2"></i>
                <input type="text" name="q" class="form-control" placeholder="Cari jasa, misal: desain logo">
                <button class="btn btn-primary" type="submit">Cari</button>
            </form>

            <div class="d-flex flex-wrap gap-2 mb-4">
                @foreach ($catItems->take(4) as $cat)
                    <a href="{{ $cat['slug'] ? route('catalog', ['category' => $cat['slug']]) : route('catalog') }}" class="chip">
                        <i class="bi {{ CategoryStyle::icon($cat['name']) }}"></i>{{ $cat['name'] }}
                    </a>
                @endforeach
            </div>

            @if ($sellerInitials->isNotEmpty())
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-stack d-flex">
                        @foreach ($sellerInitials as $i => $name)
                            @php $tones = [['#eff5ff', '#2563eb'], ['#e8f7ee', '#16a34a'], ['#fff6e0', '#d97706'], ['#f1eeff', '#7c3aed']]; @endphp
                            <span style="background: {{ $tones[$i % 4][0] }}; color: {{ $tones[$i % 4][1] }};">{{ strtoupper(mb_substr($name, 0, 1)) }}</span>
                        @endforeach
                    </div>
                    <div class="small text-secondary lh-sm">
                        <strong class="text-dark">{{ $stats['sellers'] }} penjual terverifikasi</strong><br>
                        siap mengerjakan proyekmu
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-6 d-none d-lg-block">
            @if ($preview->isNotEmpty())
                <div class="preview">
                    @if ($stats['rating'])
                        <div class="float-chip top">
                            <i class="bi bi-star-fill text-warning"></i>
                            {{ number_format($stats['rating'], 1, ',', '') }} rating rata-rata
                        </div>
                    @endif

                    @foreach ($preview as $i => $service)
                        <div class="{{ $i === 1 ? 'p-2nd' : '' }}" style="position: relative; z-index: {{ 2 - $i }};">
                            @include('public.partials.service-card', ['service' => $service])
                        </div>
                    @endforeach

                    <div class="float-chip bottom">
                        <span class="icon-tile green" style="width:28px;height:28px;font-size:.85rem;"><i class="bi bi-shield-check"></i></span>
                        Dana aman di escrow
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ================= STATS ================= --}}
<section class="card stats-bar mb-5">
    <div class="row g-0 text-center">
        <div class="col-6 col-md-3 stat py-4">
            <div class="num">{{ number_format($stats['services'], 0, ',', '.') }}</div>
            <div class="small text-secondary">Jasa aktif</div>
        </div>
        <div class="col-6 col-md-3 stat py-4">
            <div class="num">{{ number_format($stats['sellers'], 0, ',', '.') }}</div>
            <div class="small text-secondary">Penjual terverifikasi</div>
        </div>
        <div class="col-6 col-md-3 stat py-4">
            <div class="num">{{ number_format($stats['completed'], 0, ',', '.') }}</div>
            <div class="small text-secondary">Pesanan selesai</div>
        </div>
        <div class="col-6 col-md-3 stat py-4">
            <div class="num">{{ $stats['rating'] ? number_format($stats['rating'], 1, ',', '') : '-' }}</div>
            <div class="small text-secondary">Rating rata-rata</div>
        </div>
    </div>
</section>

{{-- ================= KATEGORI ================= --}}
<section id="kategori" class="mb-5 pt-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
        <div>
            <h2 class="sec-title">Jelajahi kategori</h2>
            <p class="sec-sub">Mulai dari bidang yang paling kamu butuhkan.</p>
        </div>
        <a href="{{ route('catalog') }}" class="fw-semibold text-decoration-none small">Semua kategori <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row g-3">
        @foreach ($catItems as $cat)
            <div class="col-6 col-md-4 col-lg-3">
                <a href="{{ $cat['slug'] ? route('catalog', ['category' => $cat['slug']]) : route('catalog') }}" class="cat-tile">
                    <span class="icon-tile {{ CategoryStyle::tone($cat['name']) }}"><i class="bi {{ CategoryStyle::icon($cat['name']) }}"></i></span>
                    <span class="lh-sm">
                        <span class="d-block fw-bold">{{ $cat['name'] }}</span>
                        <span class="small text-secondary">{{ $cat['count'] !== null ? $cat['count'] . ' jasa' : 'Segera hadir' }}</span>
                    </span>
                    <i class="bi bi-arrow-right arrow d-none d-md-inline"></i>
                </a>
            </div>
        @endforeach
    </div>
</section>

{{-- ================= JASA TERBARU ================= --}}
<section id="jasa" class="mb-5 pt-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2 mb-4">
        <div>
            <h2 class="sec-title">Jasa terbaru</h2>
            <p class="sec-sub">Baru ditayangkan oleh penjual di marketplace.</p>
        </div>
        <a href="{{ route('catalog') }}" class="fw-semibold text-decoration-none small">Lihat semua jasa <i class="bi bi-arrow-right"></i></a>
    </div>

    @if ($services->isNotEmpty())
        <div class="row g-4">
            @foreach ($services as $service)
                <div class="col-md-6 col-lg-4">
                    @include('public.partials.service-card', ['service' => $service])
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state
            title="Belum ada jasa"
            description="Jadilah penjual pertama yang menayangkan jasa di sini."
            icon="bi-shop"
            actionText="Mulai jual jasa"
            :actionUrl="route('register', ['role' => 'seller'])"
        />
    @endif
</section>

{{-- ================= CARA KERJA ================= --}}
<section id="cara-kerja" class="mb-5 pt-4">
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
                <span class="icon-tile mb-3"><i class="bi bi-bag-check"></i></span>
                <h3 class="h4 fw-bold mb-2">Untuk pembeli</h3>
                <p class="text-secondary mb-4">Temukan orang yang tepat tanpa khawatir uang hilang.</p>
                <ul class="list-unstyled check-list mb-4">
                    <li><i class="bi bi-check-circle-fill"></i>Dana aman di escrow sampai kamu setuju</li>
                    <li><i class="bi bi-check-circle-fill"></i>Harga dan jatah revisi jelas sebelum bayar</li>
                    <li><i class="bi bi-check-circle-fill"></i>Admin membantu kalau terjadi sengketa</li>
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
                <span class="icon-tile green mb-3"><i class="bi bi-briefcase"></i></span>
                <h3 class="h4 fw-bold mb-2">Untuk penjual</h3>
                <p class="text-secondary mb-4">Tawarkan keahlianmu dan terima pembayaran yang pasti.</p>
                <ul class="list-unstyled check-list mb-4">
                    <li><i class="bi bi-check-circle-fill"></i>Pembeli sudah bayar di depan sebelum kamu mulai</li>
                    <li><i class="bi bi-check-circle-fill"></i>Atur sendiri paket, harga, dan batas revisi</li>
                    <li><i class="bi bi-check-circle-fill"></i>Tarik saldo langsung ke rekening bank</li>
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

{{-- ================= CTA ================= --}}
<section class="cta p-4 p-lg-5 mb-2">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <h2 class="sec-title mb-2">Siap mulai proyek pertamamu?</h2>
            <p class="sec-sub">Daftar gratis, temukan penjual yang cocok, dan bayar dengan aman.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">Buka dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Daftar gratis</a>
            @endauth
        </div>
    </div>
</section>

@endsection

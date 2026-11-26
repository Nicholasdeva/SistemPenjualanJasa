<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') · {{ config('app.name', 'Marketplace Jasa') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}" rel="stylesheet">

    <style>
        .auth-split { min-height: 100vh; }
        @media (min-width: 992px) {
            html, body { height: 100%; overflow: hidden; }
            .auth-split, .auth-hero, .auth-panel { height: 100vh; }
            .auth-panel { overflow-y: auto; }
        }
        .auth-hero { background: linear-gradient(160deg, #e6efff 0%, #d3e2ff 100%); padding: 1.25rem; }
        .auth-hero-card {
            height: 100%; overflow: hidden; background: #fff; border: 1px solid #c9d8f5; border-radius: 1.5rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .07);
            padding: 1.5rem 3rem; display: flex; flex-direction: column;
        }
        .auth-hero h1 { font-size: clamp(1.6rem, 2.6vw, 2.2rem); font-weight: 700; line-height: 1.2; letter-spacing: -.01em; }
        .auth-hero h1 .hl { color: #2563eb; }
        .auth-hero-lead { font-size: 1rem; color: #5a6880; max-width: 520px; line-height: 1.65; }
        .avatar-stack span {
            width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700; border: 2px solid #fff; margin-left: -8px;
        }
        .avatar-stack span:first-child { margin-left: 0; }
        .preview-wrap { margin-top: 1.5rem; }
        .preview { position: relative; max-width: 380px; margin: 0 auto 0 0; padding-top: .5rem; }
        .mini-card {
            display: block; background: #fff; border: 1px solid #dfe5ee; border-radius: 1rem; padding: .85rem 1rem;
            box-shadow: 0 14px 28px -18px rgba(15, 23, 42, .35); text-decoration: none; color: inherit;
        }
        .mini-card:hover { color: inherit; border-color: #2563eb; }
        .mini-card + .mini-card { margin: .75rem 0 0 2.5rem; }
        .mini-card .m-title { font-weight: 700; font-size: .9rem; color: #0f172a; margin: .55rem 0 .6rem;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .auth-brand { display: inline-flex; align-items: center; gap: .6rem; font-weight: 700; color: #0f172a; text-decoration: none; }
        .auth-hero-text { margin-top: 1.5rem; }
        .float-chip {
            position: absolute; z-index: 3; background: #fff; border: 1px solid #cbd5e3; border-radius: .85rem;
            padding: .55rem .8rem; font-size: .8rem; font-weight: 600; color: #0f172a;
            box-shadow: 0 12px 28px -12px rgba(15, 23, 42, .3); display: flex; align-items: center; gap: .5rem;
        }
        .float-chip.top { top: -10px; right: -6px; }
        .float-chip.bottom { bottom: -14px; right: -6px; }
        .auth-panel { background: var(--surface, #fff); }
        .auth-form h1 { font-size: 1.6rem; }
        .auth-form { width: 100%; max-width: 420px; }
        .auth-panel { padding-top: 1.5rem !important; padding-bottom: 1.5rem !important; }
        @media (min-width: 992px) and (max-height: 980px) {
            .auth-avatars { display: none !important; }
            .auth-form .mb-3 { margin-bottom: .75rem !important; }
            .auth-form h1 + p { margin-bottom: 1rem !important; }
        }
        @media (min-width: 992px) and (max-height: 560px) { .mini-card + .mini-card { display: none; } }
        @media (min-width: 992px) and (max-height: 480px) { .auth-hero-lead { display: none; } }
    </style>
</head>
<body>
<div class="container-fluid p-0">
    <div class="row g-0 auth-split">

        <!-- Sisi kiri: tampilan -->
        <div class="col-lg-6 d-none d-lg-block auth-hero">
            <div class="auth-hero-card">
                <a href="{{ url('/') }}" class="auth-brand">
                    <span class="brand-mark"><i class="bi bi-briefcase-fill"></i></span>Marketplace Jasa
                </a>

                <div class="auth-hero-text">
                    <h1 class="mb-3">Kerjakan proyekmu bareng <span class="hl">freelancer terverifikasi</span></h1>
                    <p class="auth-hero-lead mb-3">
                        Pesan jasa desain, penulisan, sampai website dengan harga paket yang jelas.
                        Dana kamu ditahan di escrow dan baru diteruskan setelah hasilnya kamu setujui.
                    </p>

                    @if ($previewStats['sellers'] > 0)
                        @php $tones = [['#eff5ff', '#2563eb'], ['#e8f7ee', '#16a34a'], ['#fff6e0', '#d97706'], ['#f1eeff', '#7c3aed']]; @endphp
                        <div class="auth-avatars d-flex align-items-center gap-3">
                            <div class="avatar-stack d-flex">
                                @foreach ($previewServices->pluck('seller.name')->filter()->unique()->take(4)->values() as $i => $name)
                                    <span style="background: {{ $tones[$i % 4][0] }}; color: {{ $tones[$i % 4][1] }};">{{ strtoupper(mb_substr($name, 0, 1)) }}</span>
                                @endforeach
                            </div>
                            <div class="small text-secondary lh-sm">
                                <strong class="text-dark">{{ $previewStats['sellers'] }} penjual terverifikasi</strong><br>
                                siap mengerjakan proyekmu
                            </div>
                        </div>
                    @endif
                </div>

                @if ($previewServices->isNotEmpty())
                    <div class="preview-wrap">
                        <div class="preview w-100">
                            @if ($previewStats['rating'])
                                <div class="float-chip top">
                                    <i class="bi bi-star-fill text-warning"></i>
                                    {{ number_format($previewStats['rating'], 1, ',', '') }} rating rata-rata
                                </div>
                            @endif

                            @foreach ($previewServices as $service)
                                @php
                                    $catName = $service->category->name ?? 'Lainnya';
                                    $sellerName = $service->seller->name ?? 'Penjual';
                                    $cheapest = $service->packages->first();
                                @endphp
                                <a href="{{ route('catalog.show', $service) }}" class="mini-card">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="icon-tile {{ \App\Support\CategoryStyle::tone($catName) }}" style="width:28px;height:28px;font-size:.8rem;">
                                            <i class="bi {{ \App\Support\CategoryStyle::icon($catName) }}"></i>
                                        </span>
                                        <span class="small fw-semibold text-secondary">{{ $catName }}</span>
                                        @if ($service->reviews_count > 0)
                                            <span class="ms-auto small fw-semibold text-nowrap">
                                                <i class="bi bi-star-fill text-warning"></i> {{ number_format($service->reviews_avg_rating, 1, ',', '') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="m-title">{{ $service->title }}</div>
                                    <div class="d-flex align-items-center small">
                                        <span class="text-secondary text-truncate">{{ $sellerName }}</span>
                                        @if ($cheapest)
                                            <span class="ms-auto fw-bold text-nowrap">Rp{{ number_format((float) $cheapest->price, 0, ',', '.') }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach

                            <div class="float-chip bottom">
                                <span class="icon-tile green" style="width:28px;height:28px;font-size:.85rem;"><i class="bi bi-shield-check"></i></span>
                                Dana aman di escrow
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sisi kanan: form -->
        <div class="col-12 col-lg-6 auth-panel d-flex flex-column align-items-center justify-content-center p-4 p-md-5">
            <a href="{{ url('/') }}" class="d-lg-none d-inline-flex align-items-center gap-2 text-decoration-none fw-bold text-dark mb-4">
                <span class="brand-mark"><i class="bi bi-briefcase-fill"></i></span>
                Marketplace Jasa
            </a>

            <div class="auth-form">
                @foreach (['status' => 'success', 'success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $type)
                    @if (session($key))
                        <x-alert :type="$type" class="mb-4">{{ session($key) }}</x-alert>
                    @endif
                @endforeach

                @yield('content')
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('styles')
@stack('scripts')
</body>
</html>

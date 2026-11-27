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
        .auth-hero {
            position: relative; overflow: hidden; color: #fff;
            background: #0f172a url('{{ asset('images/auth-bg.jpg') }}') 38% center / cover no-repeat;
        }
        .auth-hero::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(15,23,42,.6) 0%, rgba(15,23,42,.1) 25%, rgba(15,23,42,.55) 55%, rgba(15,23,42,.92) 100%);
        }
        .auth-hero-inner { position: relative; z-index: 1; height: 100%; min-height: 0; padding: 2rem 3rem; display: flex; flex-direction: column; justify-content: space-between; }
        .auth-brand { display: inline-flex; align-items: center; gap: .6rem; font-weight: 700; color: #fff; text-decoration: none; font-size: 1.15rem; }
        .auth-brand img, .auth-brand-dark img { height: 42px; width: auto; }
        .auth-brand img { background: #fff; border-radius: .6rem; padding: 3px 5px; }
        .auth-brand-dark { display: inline-flex; align-items: center; gap: .6rem; font-weight: 700; color: #0f172a; text-decoration: none; }
        .auth-hero h2 { color: #fff !important; text-shadow: 0 2px 14px rgba(0,0,0,.35); font-size: clamp(1.7rem, 2.8vw, 2.5rem); font-weight: 700; line-height: 1.15; letter-spacing: -.02em; }
        .auth-points { list-style: none; padding: 0; margin: 1.25rem 0 0; display: flex; flex-wrap: wrap; gap: .5rem .6rem; }
        .auth-points li {
            display: inline-flex; align-items: center; gap: .4rem; padding: .4rem .9rem; font-size: .85rem; font-weight: 600;
            background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.3); border-radius: 999px; backdrop-filter: blur(6px);
        }
        .auth-panel { background: #fff; }
        .auth-form { width: 100%; max-width: 420px; }
        .auth-form h1 { font-size: 1.8rem; letter-spacing: -.02em; }
        .auth-form .form-control { padding: .7rem .95rem; border-radius: .75rem; }
        .auth-form .btn-lg { border-radius: 999px; font-weight: 600; }
        @media (min-width: 992px) {
            html, body { height: 100%; }
            .auth-split, .auth-panel { min-height: 100vh; }
            .auth-hero { position: sticky; top: 0; height: 100vh; align-self: flex-start; }
        }
    </style>
</head>
<body>
<div class="container-fluid p-0">
    <div class="row g-0 auth-split">

        <!-- Sisi kiri: foto -->
        <div class="col-lg-6 d-none d-lg-block auth-hero">
            <div class="auth-hero-inner">
                <a href="{{ url('/') }}" class="auth-brand">
                    <img src="{{ asset('images/logo.png') }}" alt="">Marketplace Jasa
                </a>

                <div>
                    <h2 class="mb-2">Kerjakan proyekmu bareng freelancer terverifikasi</h2>
                    <p class="mb-0" style="color:#e2e8f0; max-width: 480px;">
                        Harga paket jelas, dana ditahan di escrow dan baru diteruskan setelah hasilnya kamu setujui.
                    </p>
                    <ul class="auth-points">
                        <li><i class="bi bi-shield-check"></i> Escrow aman</li>
                        <li><i class="bi bi-patch-check"></i> Penjual terverifikasi</li>
                        <li><i class="bi bi-chat-dots"></i> Workspace pesanan</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sisi kanan: form -->
        <div class="col-12 col-lg-6 auth-panel d-flex flex-column align-items-center justify-content-center p-4 p-md-5">
            <a href="{{ url('/') }}" class="d-lg-none auth-brand-dark mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="">Marketplace Jasa
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

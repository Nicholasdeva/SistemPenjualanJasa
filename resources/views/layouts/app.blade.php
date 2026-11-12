<!DOCTYPE html>
<html lang="id" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title') @yield('title') — {{ config('app.name', 'Marketplace Jasa') }} @else {{ config('app.name', 'Marketplace Jasa') }} @endif</title>

    <!-- Bootstrap 5.3.3 CSS & Bootstrap Icons 1.11.3 (Dikunci: Bootstrap 5, tidak dicampur Tailwind) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Tema bersama (menimpa variabel Bootstrap, lihat public/css/theme.css) -->
    <link href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="d-flex flex-column h-100">

@php
    $currentUser = auth()->user();
    $userRole = $currentUser->role ?? null;
    $showSidebar = auth()->check() && !View::hasSection('hide_sidebar');

    $roleLabel = match($userRole) {
        'buyer'  => ['text' => 'Pembeli', 'class' => 'text-bg-primary'],
        'seller' => ['text' => 'Penjual', 'class' => 'text-bg-success'],
        'admin'  => ['text' => 'Admin', 'class' => 'text-bg-warning'],
        default  => ['text' => 'Pengunjung', 'class' => 'text-bg-secondary'],
    };
@endphp

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top app-navbar">
    <div class="container-fluid px-3 px-lg-4">
        @if ($showSidebar)
            <button class="btn btn-outline-secondary btn-sm d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#roleSidebarOffcanvas" aria-controls="roleSidebarOffcanvas">
                <i class="bi bi-list fs-5"></i>
            </button>
        @endif

        <a class="navbar-brand d-flex align-items-center gap-2 me-lg-4" href="{{ url('/') }}">
            <span class="brand-mark"><i class="bi bi-briefcase-fill"></i></span>
            <span>Marketplace Jasa</span>
            @auth
                <span class="badge {{ $roleLabel['class'] }} navbar-brand-badge">{{ $roleLabel['text'] }}</span>
            @endauth
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <!-- Left Navbar Links (Berbeda per peran) -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                        <i class="bi bi-house-door me-1"></i>Beranda
                    </a>
                </li>
                @if (Route::has('catalog'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('catalog*') ? 'active' : '' }}" href="{{ route('catalog') }}">
                            <i class="bi bi-grid me-1"></i>Katalog Jasa
                        </a>
                    </li>
                @endif

                {{-- Menu khusus peran ada di sidebar kiri (dan offcanvas di HP), di sini cukup tautan ke dashboard --}}
                @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs($userRole . ".*") || request()->routeIs("workspace.*") ? "active" : "" }}" href="{{ route("dashboard") }}">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                @endauth
            </ul>

            <!-- Right Navbar Links -->
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-dark" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            @if ($currentUser->avatar_url)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($currentUser->avatar_url) }}"
                                     alt="Foto profil {{ $currentUser->name }}" class="avatar-circle object-fit-cover">
                            @else
                                <span class="avatar-circle">{{ strtoupper(substr($currentUser->name, 0, 1)) }}</span>
                            @endif
                            <span>{{ $currentUser->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-semibold small">{{ $currentUser->name }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ $currentUser->email }}</div>
                                <span class="badge {{ $roleLabel['class'] }} mt-1">{{ $roleLabel['text'] }}</span>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('dashboard') }}">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard Saya
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person me-2"></i>Edit Profil
                                </a>
                            </li>
                            @if ($userRole === 'seller')
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('seller.profile.edit') }}">
                                        <i class="bi bi-person-badge me-2"></i>Profil & Rekening
                                    </a>
                                </li>
                            @elseif ($userRole === 'buyer')
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('buyer.orders.index') }}">
                                        <i class="bi bi-bag-check me-2"></i>Pesanan Saya
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="btn btn-primary px-3" href="{{ route('register') }}">Daftar gratis</a>
                        </li>
                    @endif
                @endauth
            </ul>
        </div>
    </div>
</nav>

<!-- Main Wrapper (Sidebar + Content) -->
<div class="container-fluid flex-grow-1">
    <div class="row">
        @if ($showSidebar)
            <!-- Sidebar Desktop & Offcanvas Mobile (Berbeda per peran: buyer, seller, admin) -->
            <aside class="col-lg-3 col-xl-2 d-none d-lg-block app-sidebar py-3 px-2">
                @include('layouts.partials.sidebar-menu', ['userRole' => $userRole])
            </aside>

            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="roleSidebarOffcanvas" aria-labelledby="roleSidebarOffcanvasLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title fw-bold" id="roleSidebarOffcanvasLabel">
                        <i class="bi bi-briefcase-fill text-primary me-1"></i>Menu {{ $roleLabel['text'] }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body app-sidebar p-2">
                    @include('layouts.partials.sidebar-menu', ['userRole' => $userRole])
                </div>
            </div>
        @endif

        <!-- Main Content Area -->
        @php $fullWidth = View::hasSection('full_width'); @endphp
        <main class="{{ $fullWidth ? 'col-12 p-0' : ($showSidebar ? 'col-12 col-lg-9 col-xl-10 px-3 px-md-4 py-4' : 'col-12 py-4') }}">
            <div class="{{ $fullWidth || $showSidebar ? '' : 'container' }}">
                <!-- Area Pesan Flash -->
                <div class="{{ $fullWidth ? 'wrap pt-4' : '' }}">
                @if (session('status'))
                    <x-alert type="success" class="mb-4">
                        {{ session('status') }}
                    </x-alert>
                @endif

                @if (session('success'))
                    <x-alert type="success" class="mb-4">
                        {{ session('success') }}
                    </x-alert>
                @endif

                @if (session('error'))
                    <x-alert type="danger" class="mb-4">
                        {{ session('error') }}
                    </x-alert>
                @endif

                @if (session('warning'))
                    <x-alert type="warning" class="mb-4">
                        {{ session('warning') }}
                    </x-alert>
                @endif

                @if (session('info'))
                    <x-alert type="info" class="mb-4">
                        {{ session('info') }}
                    </x-alert>
                @endif
                </div>

                @yield('content')
            </div>
        </main>
    </div>
</div>

<!-- Footer -->
<footer class="app-footer mt-auto py-4">
    <div class="container-fluid px-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="brand-mark" style="width:24px;height:24px;font-size:.75rem;box-shadow:none;"><i class="bi bi-briefcase-fill"></i></span>
                <span><strong class="text-dark">Marketplace Jasa</strong> &copy; {{ date('Y') }} · Proyek UTS Pemrograman Web Lanjut</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span><i class="bi bi-shield-check text-primary me-1"></i>Transaksi aman dengan escrow</span>
                <span>Tim: Faqih, Nico, Annis, Fiki</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

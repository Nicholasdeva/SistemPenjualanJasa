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

    <style>
        :root {
            --brand-primary: #2563eb;
            --brand-dark: #0f172a;
        }
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }
        .navbar-brand-badge {
            font-size: 0.7rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .app-sidebar {
            min-height: calc(100vh - 56px);
            background: #ffffff;
            border-right: 1px solid #e2e8f0;
        }
        .app-sidebar .nav-link {
            color: #475569;
            font-weight: 500;
            padding: 0.6rem 0.9rem;
            border-radius: 0.5rem;
            margin-bottom: 0.2rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            transition: all 0.15s ease-in-out;
        }
        .app-sidebar .nav-link:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .app-sidebar .nav-link.active {
            background-color: #eff6ff;
            color: #2563eb;
            font-weight: 600;
        }
        .sidebar-heading {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #94a3b8;
            padding: 0.75rem 0.9rem 0.35rem;
        }
        .card {
            border-color: #e2e8f0;
            border-radius: 0.75rem;
        }
        .card-header {
            border-bottom-color: #f1f5f9;
            border-top-left-radius: 0.75rem !important;
            border-top-right-radius: 0.75rem !important;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column h-100">

@php
    $currentUser = auth()->user();
    $userRole = $currentUser->role ?? null;
    $showSidebar = auth()->check() && !View::hasSection('hide_sidebar');

    $roleLabel = match($userRole) {
        'buyer'  => ['text' => 'Pembeli', 'class' => 'text-bg-primary'],
        'seller' => ['text' => 'Penjual Jasa', 'class' => 'text-bg-success'],
        'admin'  => ['text' => 'Administrator', 'class' => 'text-bg-danger'],
        default  => ['text' => 'Pengunjung', 'class' => 'text-bg-secondary'],
    };
@endphp

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid px-3 px-lg-4">
        @if ($showSidebar)
            <button class="btn btn-outline-secondary btn-sm d-lg-none me-2 text-light border-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#roleSidebarOffcanvas" aria-controls="roleSidebarOffcanvas">
                <i class="bi bi-list fs-5"></i>
            </button>
        @endif

        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="{{ url('/') }}">
            <i class="bi bi-briefcase-fill text-primary"></i>
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

                @auth
                    @if ($userRole === 'buyer')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}" href="{{ route('buyer.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('buyer.orders.*') ? 'active' : '' }}" href="{{ route('buyer.orders.index') }}">Pesanan Saya</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('buyer.checkout*') ? 'active' : '' }}" href="{{ route('buyer.checkout') }}">Checkout</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('workspace.*') ? 'active' : '' }}" href="{{ route('workspace.show', 1) }}">Workspace</a>
                        </li>
                    @elseif ($userRole === 'seller')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}" href="{{ route('seller.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.services.*') ? 'active' : '' }}" href="{{ route('seller.services.index') }}">Jasa Saya</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}" href="{{ route('seller.orders.index') }}">Pesanan Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('workspace.*') ? 'active' : '' }}" href="{{ route('workspace.show', 1) }}">Workspace</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.profile.*') ? 'active' : '' }}" href="{{ route('seller.profile.edit') }}">Profil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('seller.withdrawals.*') ? 'active' : '' }}" href="{{ route('seller.withdrawals.index') }}">Tarik Saldo</a>
                        </li>
                    @elseif ($userRole === 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}" href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin/dashboard') }}">Dashboard Admin</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/sellers*') ? 'active' : '' }}" href="{{ Route::has('admin.sellers.index') ? route('admin.sellers.index') : url('/admin/sellers') }}">Verifikasi Penjual</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/disputes*') ? 'active' : '' }}" href="{{ Route::has('admin.disputes.index') ? route('admin.disputes.index') : url('/admin/disputes') }}">Dispute Center</a>
                        </li>
                    @endif
                @endauth
            </ul>

            <!-- Right Navbar Links -->
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-semibold" style="width: 30px; height: 30px; font-size: 0.85rem;">
                                {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                            </span>
                            <span>{{ $currentUser->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
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
                        <a class="btn btn-outline-light btn-sm px-3" href="{{ route('login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm px-3" href="{{ route('register') }}">
                                <i class="bi bi-person-plus me-1"></i>Daftar
                            </a>
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
        <main class="{{ $showSidebar ? 'col-12 col-lg-9 col-xl-10 px-3 px-md-4 py-4' : 'col-12 py-4' }}">
            <div class="{{ $showSidebar ? '' : 'container' }}">
                <!-- Area Pesan Flash -->
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

                @yield('content')
            </div>
        </main>
    </div>
</div>

<!-- Footer -->
<footer class="bg-white border-top mt-auto py-3">
    <div class="container-fluid px-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted gap-2">
            <div>
                <strong class="text-dark">Marketplace Penjualan Jasa</strong> &copy; {{ date('Y') }} — Proyek UTS Pemrograman Web Lanjut (Laravel &amp; PostgreSQL).
            </div>
            <div class="d-flex align-items-center gap-3">
                <span><i class="bi bi-shield-check text-success me-1"></i>Transaksi Aman dengan Escrow</span>
                <span class="text-secondary">|</span>
                <span>Tim: Faqih, Nico, Annis, Fiki</span>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>

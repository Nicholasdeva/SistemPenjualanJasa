@if ($userRole === 'buyer')
    <div class="sidebar-heading">Area Pembeli (Klien)</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('buyer.dashboard') ? 'active' : '' }}" href="{{ route('buyer.dashboard') }}">
            <i class="bi bi-grid-1x2"></i>
            <span>Dashboard Pembeli</span>
        </a>
        <a class="nav-link {{ request()->routeIs('buyer.orders.*') ? 'active' : '' }}" href="{{ route('buyer.orders.index') }}">
            <i class="bi bi-bag-check"></i>
            <span>Pesanan Saya</span>
        </a>
        <a class="nav-link {{ request()->routeIs('buyer.checkout*') ? 'active' : '' }}" href="{{ route('buyer.checkout') }}">
            <i class="bi bi-credit-card"></i>
            <span>Checkout &amp; Bayar</span>
        </a>
        <a class="nav-link {{ request()->routeIs('workspace.*') ? 'active' : '' }}" href="{{ route('workspace.show', 1) }}">
            <i class="bi bi-chat-left-dots"></i>
            <span>Workspace Pesanan</span>
        </a>
        <a class="nav-link {{ request()->routeIs('buyer.reviews.*') ? 'active' : '' }}" href="{{ route('buyer.reviews.create', 1) }}">
            <i class="bi bi-star"></i>
            <span>Form Ulasan Jasa</span>
        </a>
    </nav>

    <div class="sidebar-heading mt-3">Eksplorasi</div>
    <nav class="nav flex-column">
        <a class="nav-link" href="{{ Route::has('catalog') ? route('catalog') : url('/') }}">
            <i class="bi bi-search"></i>
            <span>Cari Jasa Baru</span>
        </a>
    </nav>

    <div class="p-3 mt-4 rounded-3 bg-light border small">
        <div class="fw-semibold text-dark mb-1"><i class="bi bi-shield-lock text-primary me-1"></i>Garansi Escrow</div>
        <div class="text-muted" style="font-size: 0.78rem;">Dana Anda ditahan dengan aman di rekening bersama hingga hasil kerja penjual Anda setujui.</div>
    </div>

@elseif ($userRole === 'seller')
    <div class="sidebar-heading">Area Penjual Jasa</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}" href="{{ route('seller.dashboard') }}">
            <i class="bi bi-speedometer2"></i>
            <span>Dashboard Penjual</span>
        </a>
        <a class="nav-link {{ request()->routeIs('seller.services.index') ? 'active' : '' }}" href="{{ route('seller.services.index') }}">
            <i class="bi bi-collection"></i>
            <span>Daftar Jasa Saya</span>
        </a>
        <a class="nav-link {{ request()->routeIs('seller.services.create') ? 'active' : '' }}" href="{{ route('seller.services.create') }}">
            <i class="bi bi-plus-circle"></i>
            <span>Tambah Jasa Baru</span>
        </a>
        <a class="nav-link {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}" href="{{ route('seller.orders.index') }}">
            <i class="bi bi-inbox"></i>
            <span>Pesanan Masuk</span>
        </a>
        <a class="nav-link {{ request()->routeIs('workspace.*') ? 'active' : '' }}" href="{{ route('workspace.show', 1) }}">
            <i class="bi bi-chat-square-text"></i>
            <span>Workspace Kerja</span>
        </a>
    </nav>

    <div class="sidebar-heading mt-3">Keuangan &amp; Verifikasi</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->routeIs('seller.withdrawals.*') ? 'active' : '' }}" href="{{ route('seller.withdrawals.index') }}">
            <i class="bi bi-wallet2"></i>
            <span>Tarik Saldo</span>
        </a>
        <a class="nav-link {{ request()->routeIs('seller.profile.*') ? 'active' : '' }}" href="{{ route('seller.profile.edit') }}">
            <i class="bi bi-person-vcard"></i>
            <span>Profil Penjual</span>
        </a>
    </nav>

@elseif ($userRole === 'admin')
    <div class="sidebar-heading">Panel Administrator</div>
    <nav class="nav flex-column">
        <a class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}" href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : url('/admin/dashboard') }}">
            <i class="bi bi-bar-chart-line"></i>
            <span>Dashboard Admin</span>
        </a>
        <a class="nav-link {{ request()->is('admin/sellers*') ? 'active' : '' }}" href="{{ Route::has('admin.sellers.index') ? route('admin.sellers.index') : url('/admin/sellers') }}">
            <i class="bi bi-patch-check"></i>
            <span>Verifikasi Penjual</span>
        </a>
        <a class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}" href="{{ Route::has('admin.categories.index') ? route('admin.categories.index') : url('/admin/categories') }}">
            <i class="bi bi-tags"></i>
            <span>Kelola Kategori</span>
        </a>
        <a class="nav-link {{ request()->is('admin/services*') ? 'active' : '' }}" href="{{ Route::has('admin.services.index') ? route('admin.services.index') : url('/admin/services') }}">
            <i class="bi bi-shield-shaded"></i>
            <span>Moderasi Layanan</span>
        </a>
        <a class="nav-link {{ request()->is('admin/withdrawals*') ? 'active' : '' }}" href="{{ Route::has('admin.withdrawals.index') ? route('admin.withdrawals.index') : url('/admin/withdrawals') }}">
            <i class="bi bi-cash-stack"></i>
            <span>Penarikan Dana</span>
        </a>
        <a class="nav-link {{ request()->is('admin/disputes*') ? 'active' : '' }}" href="{{ Route::has('admin.disputes.index') ? route('admin.disputes.index') : url('/admin/disputes') }}">
            <i class="bi bi-exclamation-octagon"></i>
            <span>Dispute Center</span>
        </a>
    </nav>
@endif

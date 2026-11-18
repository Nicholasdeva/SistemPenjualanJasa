@extends('layouts.app')

@section('title', 'Pembayaran Pesanan')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb small mb-1">
            <li class="breadcrumb-item"><a href="{{ route('buyer.orders.index') }}" class="text-decoration-none">Pesanan Saya</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pembayaran</li>
        </ol>
    </nav>
    <h1 class="h3 fw-bold mb-1">Pembayaran Pesanan</h1>
    <p class="text-muted mb-0">Selesaikan pembayaran lewat Midtrans. Dana ditahan di escrow sampai Anda menyetujui hasil kerja.</p>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <x-card title="Rincian Pesanan" icon="bi-receipt">
            <div class="mb-3">
                <span class="badge bg-light text-dark border me-1">{{ $order->invoice_no }}</span>
                <span class="badge bg-primary bg-opacity-10 text-primary">Paket {{ $order->servicePackage->tier_name }}</span>
            </div>
            <h5 class="fw-bold mb-1">{{ $order->servicePackage->service->title }}</h5>
            <div class="small text-muted mb-3">
                Oleh <strong class="text-dark">{{ $order->servicePackage->service->seller->name }}</strong>
                • Pengerjaan {{ $order->servicePackage->delivery_days }} hari sejak pembayaran terverifikasi
            </div>
            <div class="small fw-semibold text-dark mb-1">Brief proyek</div>
            <div class="small text-secondary bg-light border rounded p-3" style="white-space: pre-line;">{{ $order->project_brief }}</div>
        </x-card>
    </div>

    <div class="col-lg-5">
        <x-card title="Pembayaran" icon="bi-credit-card">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <span class="fw-bold text-dark">Total Pembayaran</span>
                <span class="fs-4 fw-bold text-primary">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>

            <div class="alert alert-info small py-2 px-3">
                <i class="bi bi-shield-fill-check me-1"></i>
                Dana disimpan di rekening bersama platform dan baru diteruskan ke penjual setelah Anda menyetujui hasil kerja.
            </div>

            <button type="button" id="pay-button" class="btn btn-primary btn-lg w-100 fw-semibold">
                <i class="bi bi-lock-fill me-1"></i>Bayar Sekarang
            </button>
            <div id="pay-message" class="small text-danger mt-2"></div>

            <div class="text-center mt-2 small text-muted">
                <i class="bi bi-credit-card-2-front me-1"></i>QRIS, Virtual Account, E-Wallet, Kartu (Midtrans Sandbox)
            </div>

            @if (app()->environment('local'))
                <form action="{{ route('buyer.orders.simulate-pay', $order->id) }}" method="POST" class="mt-3 pt-3 border-top"
                      onsubmit="return confirm('Simulasi bayar hanya untuk demo lokal. Lanjutkan?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-cash-coin me-1"></i>Simulasi Bayar (khusus lokal)
                    </button>
                </form>
            @endif
        </x-card>
    </div>
</div>

<script
    src="{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
    data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script>
    (function () {
        const tokenUrl  = @json(route('buyer.orders.snap-token', $order->id));
        const syncUrl   = @json(route('buyer.orders.sync-payment', $order->id));
        const ordersUrl = @json(route('buyer.orders.index'));
        const csrf      = @json(csrf_token());
        const button    = document.getElementById('pay-button');
        const message   = document.getElementById('pay-message');

        function post(url) {
            return fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            }).then(function (res) {
                return res.json().then(function (data) {
                    if (!res.ok) { throw new Error(data.message || 'Terjadi kesalahan.'); }
                    return data;
                });
            });
        }

        function finish() {
            message.textContent = '';
            post(syncUrl)
                .then(function (data) { window.location.href = data.redirect; })
                .catch(function () { window.location.href = ordersUrl; });
        }

        function startPayment() {
            button.disabled = true;
            message.textContent = '';

            post(tokenUrl).then(function (data) {
                window.snap.pay(data.token, {
                    onSuccess: finish,
                    onPending: finish,
                    onError: function () {
                        message.textContent = 'Pembayaran gagal. Silakan coba lagi.';
                        button.disabled = false;
                    },
                    onClose: function () { button.disabled = false; },
                });
            }).catch(function (err) {
                message.textContent = err.message;
                button.disabled = false;
            });
        }

        button.addEventListener('click', startPayment);
        startPayment(); // popup terbuka otomatis
    })();
</script>
@endsection
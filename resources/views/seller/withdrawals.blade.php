@extends('layouts.app')

@section('title', 'Tarik Saldo')

@php
    $statusLabel = [
        'pending'  => ['Menunggu', 'text-bg-warning'],
        'approved' => ['Disetujui', 'text-bg-success'],
        'rejected' => ['Ditolak', 'text-bg-danger'],
    ];
@endphp

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Tarik Saldo</h1>
    <p class="text-secondary small mb-0">Cairkan saldo wallet ke rekening yang terdaftar di profil.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="small text-secondary">Saldo tersedia</div>
                <div class="fs-3 mono mb-4">Rp{{ number_format($wallet, 0, ',', '.') }}</div>

                <form action="{{ route('seller.withdrawals.store') }}" method="POST">
                    @csrf
                    <label for="amount" class="form-label small fw-semibold">Nominal penarikan</label>
                    <div class="input-group mb-1 has-validation">
                        <span class="input-group-text">Rp</span>
                        <input type="number" id="amount" name="amount" min="10000" step="1000"
                               class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-text mb-3">Minimal Rp10.000.</div>
                    <button class="btn btn-primary w-100">Ajukan Penarikan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body p-4 small text-secondary">
                <div class="fw-semibold text-dark mb-2">Ketentuan penarikan</div>
                <ul class="mb-0 ps-3">
                    <li class="mb-1">Penarikan diproses oleh admin setelah permintaan diajukan.</li>
                    <li class="mb-1">Dana dikirim ke rekening yang tercatat di Profil Penjual.</li>
                    <li>Saldo escrow belum bisa ditarik sebelum pesanan disetujui pembeli.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <h2 class="h6 fw-semibold mb-0">Riwayat penarikan</h2>
    </div>
    @if ($withdrawals->isEmpty())
        <div class="card-body text-center text-secondary small py-4">Belum ada penarikan.</div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Tanggal</th><th>Nominal</th><th>Rekening</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($withdrawals as $w)
                        @php [$label, $class] = $statusLabel[$w->status] ?? [ucfirst($w->status), 'text-bg-secondary']; @endphp
                        <tr>
                            <td>{{ $w->created_at->format('d M Y') }}</td>
                            <td class="mono">Rp{{ number_format($w->amount, 0, ',', '.') }}</td>
                            <td class="text-secondary">{{ $w->bank_account }}</td>
                            <td><span class="badge {{ $class }}">{{ $label }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

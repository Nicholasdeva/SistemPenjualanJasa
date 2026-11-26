@extends('layouts.app')

@section('title', 'Arus Kas')

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Arus Kas</h1>
    <p class="text-secondary small mb-0">Pembayaran berhasil adalah dana masuk escrow, bukan pendapatan platform. Pengeluaran dihitung dari penarikan yang disetujui.</p>
</div>

<form method="GET" action="{{ route('admin.cash-flow.index') }}" class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-sm-5 col-lg-4">
                <label for="from" class="form-label small fw-semibold">Tanggal awal</label>
                <input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control">
            </div>
            <div class="col-sm-5 col-lg-4">
                <label for="to" class="form-label small fw-semibold">Tanggal akhir</label>
                <input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control">
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
        @error('to')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
        @error('from')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>
</form>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-secondary mb-1">Pembayaran masuk (status paid)</div>
                <div class="fs-4 fw-semibold">Rp{{ number_format((float) $incomeTotal, 0, ',', '.') }}</div>
                <div class="small text-secondary">Dana pembayaran yang masuk ke escrow.</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <div class="small text-secondary mb-1">Dana keluar (penarikan disetujui)</div>
                <div class="fs-4 fw-semibold">Rp{{ number_format((float) $expenseTotal, 0, ',', '.') }}</div>
                <div class="small text-secondary">Penarikan pending atau ditolak tidak dihitung sebagai pengeluaran.</div>
            </div>
        </div>
    </div>
</div>

<section class="card mb-4">
    <div class="card-header bg-white py-3">
        <h2 class="h6 fw-semibold mb-0">Pemasukan pembayaran</h2>
    </div>
    @if ($payments->isEmpty())
        <div class="card-body">
            <x-empty-state title="Tidak ada pembayaran" description="Tidak ditemukan pembayaran berhasil pada rentang tanggal ini." icon="bi-credit-card" />
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor transaksi / pesanan</th>
                        <th>Pembeli</th>
                        <th>Penjual</th>
                        <th>Metode</th>
                        <th>Nominal masuk</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td class="text-nowrap">{{ ($payment->paid_at ?? $payment->created_at)?->format('d M Y H:i') ?? '-' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $payment->transaction_id ?: $payment->midtrans_order_id ?: 'PAY-' . $payment->id }}</div>
                                <div class="small text-secondary">{{ $payment->order?->invoice_no ?? '-' }}</div>
                            </td>
                            <td>{{ $payment->order?->buyer?->name ?? '-' }}</td>
                            <td>{{ $payment->order?->servicePackage?->service?->seller?->name ?? '-' }}</td>
                            <td>{{ $payment->payment_method ?: '-' }}</td>
                            <td class="text-nowrap">Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                            <td><span class="badge text-bg-success">Paid</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $payments->links() }}</div>
    @endif
</section>

<section class="card">
    <div class="card-header bg-white py-3">
        <h2 class="h6 fw-semibold mb-0">Pengeluaran dan permintaan penarikan</h2>
    </div>
    @if ($withdrawals->isEmpty())
        <div class="card-body">
            <x-empty-state title="Tidak ada penarikan" description="Tidak ditemukan transaksi penarikan pada rentang tanggal ini." icon="bi-cash-coin" />
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nomor transaksi</th>
                        <th>Penjual</th>
                        <th>Rekening</th>
                        <th>Nominal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($withdrawals as $withdrawal)
                        @php
                            $statusClasses = [
                                'pending' => 'text-bg-warning',
                                'approved' => 'text-bg-success',
                                'rejected' => 'text-bg-danger',
                            ];
                        @endphp
                        <tr>
                            <td class="text-nowrap">{{ $withdrawal->created_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="fw-semibold">WD-{{ $withdrawal->id }}</td>
                            <td>{{ $withdrawal->seller?->name ?? '-' }}</td>
                            <td>{{ $withdrawal->bank_account }}</td>
                            <td class="text-nowrap">Rp{{ number_format((float) $withdrawal->amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $statusClasses[$withdrawal->status] ?? 'text-bg-secondary' }}">
                                    {{ ucfirst($withdrawal->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $withdrawals->links() }}</div>
    @endif
</section>
@endsection

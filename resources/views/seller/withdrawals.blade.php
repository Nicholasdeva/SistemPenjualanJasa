@extends('layouts.app')

@section('content')
<h1 class="h3 mb-3">Tarik Saldo</h1>

<p>Saldo tersedia: <strong>Rp {{ number_format($wallet, 0, ',', '.') }}</strong></p>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('seller.withdrawals.store') }}" method="POST" class="col-md-5 p-0 mb-4">
    @csrf
    <div class="mb-3">
        <label class="form-label">Nominal Penarikan (Rp)</label>
        <input type="number" name="amount" min="10000" step="1000" class="form-control" value="{{ old('amount') }}" required>
    </div>
    <button class="btn btn-success">Ajukan Penarikan</button>
</form>

<h2 class="h5">Riwayat Penarikan</h2>
@if ($withdrawals->isEmpty())
    <div class="alert alert-secondary">Belum ada penarikan.</div>
@else
    <table class="table table-sm bg-white">
        <thead class="table-light">
            <tr><th>Tanggal</th><th>Nominal</th><th>Rekening</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($withdrawals as $w)
            <tr>
                <td>{{ $w->created_at->format('d M Y') }}</td>
                <td>Rp {{ number_format($w->amount, 0, ',', '.') }}</td>
                <td>{{ $w->bank_account }}</td>
                <td><span class="badge text-bg-secondary">{{ $w->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif
@endsection
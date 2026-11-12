@extends('layouts.app')

@section('title', 'Penarikan Dana')

@php
    $statusLabel = [
        'pending'  => ['Menunggu', 'text-bg-warning'],
        'approved' => ['Disetujui', 'text-bg-success'],
        'rejected' => ['Ditolak', 'text-bg-danger'],
    ];
@endphp

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Penarikan Dana</h1>
    <p class="text-secondary small mb-0">Permintaan pencairan saldo dari penjual.</p>
</div>

@if ($withdrawals->isEmpty())
    <x-empty-state title="Belum ada permintaan" description="Belum ada permintaan penarikan dana dari penjual." icon="bi-cash-coin" />
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Penjual</th>
                        <th>Rekening</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($withdrawals as $withdrawal)
                        @php [$label, $class] = $statusLabel[$withdrawal->status] ?? [ucfirst($withdrawal->status), 'text-bg-secondary']; @endphp
                        <tr>
                            <td class="text-nowrap">{{ $withdrawal->created_at->format('d M Y') }}</td>
                            <td>{{ $withdrawal->seller->name ?? '-' }}</td>
                            <td class="text-secondary">{{ $withdrawal->bank_account }}</td>
                            <td class="mono text-nowrap">Rp{{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $class }}">{{ $label }}</span></td>
                            <td class="text-end">
                                @if ($withdrawal->status === 'pending')
                                    <form action="{{ route('admin.withdrawals.process', $withdrawal) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Proses penarikan ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-primary">Proses</button>
                                    </form>
                                @else
                                    <span class="small text-secondary">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection

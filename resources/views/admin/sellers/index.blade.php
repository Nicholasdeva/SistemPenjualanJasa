@extends('layouts.app')

@section('title', 'Verifikasi Penjual')

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Verifikasi Penjual</h1>
    <p class="text-secondary small mb-0">Periksa data KTP dan rekening sebelum menyetujui akun penjual.</p>
</div>

@if ($sellers->isEmpty())
    <x-empty-state title="Belum ada penjual" description="Belum ada akun penjual yang terdaftar." icon="bi-person-badge" />
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Penjual</th>
                        <th>Nomor KTP</th>
                        <th>Rekening</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sellers as $seller)
                        @php
                            $profile = $seller->sellerProfile;
                            $complete = $profile && $profile->ktp_number && $profile->bank_account;
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $seller->name }}</div>
                                <div class="small text-secondary">{{ $seller->email }}</div>
                            </td>
                            <td class="text-nowrap">{{ $profile?->ktp_number ?: '-' }}</td>
                            <td>
                                @if ($profile && $profile->bank_account)
                                    <div>{{ $profile->bank_name }}</div>
                                    <div class="small text-secondary">{{ $profile->bank_account }}</div>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($profile?->is_verified)
                                    <span class="badge text-bg-success">Terverifikasi</span>
                                @elseif ($complete)
                                    <span class="badge text-bg-warning">Menunggu</span>
                                @else
                                    <span class="badge text-bg-secondary">Data belum lengkap</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($profile)
                                    @if ($profile->is_verified)
                                        <form method="POST" action="{{ route('admin.sellers.unverify', $seller) }}" class="d-inline"
                                              onsubmit="return confirm('Cabut verifikasi penjual ini?')">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-outline-danger">Cabut</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.sellers.verify', $seller) }}" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-primary" @disabled(! $complete)>Setujui</button>
                                        </form>
                                    @endif
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

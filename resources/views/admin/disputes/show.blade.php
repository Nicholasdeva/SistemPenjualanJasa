@extends('layouts.app')

@section('title', 'Detail Sengketa')

@php
    $order = $dispute->order;
    $service = $order?->servicePackage?->service;
@endphp

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.disputes.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Dispute Center</a>
    <h1 class="h4 fw-bold mt-2 mb-1">Sengketa {{ $order->invoice_no ?? '#' . $dispute->order_id }}</h1>
    <p class="text-secondary small mb-0">Diajukan {{ $dispute->created_at->format('d M Y, H:i') }} oleh {{ $dispute->raiser->name ?? '-' }}</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-semibold mb-0">Alasan sengketa</h2>
            </div>
            <div class="card-body">
                <p class="mb-0" style="white-space: pre-line;">{{ $dispute->reason }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-semibold mb-0">Putusan admin</h2>
            </div>
            <div class="card-body">
                @if ($dispute->status === 'open')
                    <form action="{{ route('admin.disputes.resolve', $dispute) }}" method="POST"
                          onsubmit="return confirm('Putusan tidak bisa diubah. Lanjutkan?')">
                        @csrf
                        @method('PATCH')
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="resolution" id="res-refund" value="refund" @checked(old('resolution') === 'refund')>
                                <label class="btn btn-outline-primary w-100 text-start p-3" for="res-refund">
                                    <div class="fw-semibold">Refund ke pembeli</div>
                                    <div class="small">Dana dikembalikan, pesanan dibatalkan.</div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <input type="radio" class="btn-check" name="resolution" id="res-release" value="release" @checked(old('resolution') === 'release')>
                                <label class="btn btn-outline-primary w-100 text-start p-3" for="res-release">
                                    <div class="fw-semibold">Cairkan ke penjual</div>
                                    <div class="small">Dana diteruskan ke saldo penjual.</div>
                                </label>
                            </div>
                        </div>
                        @error('resolution')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                        <label for="admin_decision" class="form-label small fw-semibold">Catatan putusan (opsional)</label>
                        <textarea id="admin_decision" name="admin_decision" rows="3" class="form-control mb-3"
                                  placeholder="Alasan admin mengambil keputusan ini">{{ old('admin_decision') }}</textarea>

                        <button type="submit" class="btn btn-primary">Simpan Putusan</button>
                    </form>
                @else
                    <div class="fw-semibold mb-1">
                        {{ $dispute->status === 'resolved_buyer_refund' ? 'Refund ke pembeli' : 'Dicairkan ke penjual' }}
                    </div>
                    <p class="small text-secondary mb-0">{{ $dispute->admin_decision ?: 'Tidak ada catatan.' }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-semibold mb-0">Info pesanan</h2>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-5 fw-normal text-secondary">Jasa</dt>
                    <dd class="col-7">{{ $service->title ?? '-' }}</dd>
                    <dt class="col-5 fw-normal text-secondary">Pembeli</dt>
                    <dd class="col-7">{{ $order->buyer->name ?? '-' }}</dd>
                    <dt class="col-5 fw-normal text-secondary">Penjual</dt>
                    <dd class="col-7">{{ $service->seller->name ?? '-' }}</dd>
                    <dt class="col-5 fw-normal text-secondary">Total</dt>
                    <dd class="col-7">Rp{{ number_format($order->total_price ?? 0, 0, ',', '.') }}</dd>
                    <dt class="col-5 fw-normal text-secondary">Status</dt>
                    <dd class="col-7 mb-0">@if ($order)<x-status-badge :status="$order->status" />@else - @endif</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

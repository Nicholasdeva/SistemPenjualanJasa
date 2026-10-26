@extends('layouts.app')

@section('title', '500 Kesalahan Server')
@section('hide_sidebar', true)

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-7 col-lg-6 text-center">
        <div class="card shadow-sm border-0 p-4 p-md-5">
            <div class="mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-secondary bg-opacity-10 text-secondary" style="width: 80px; height: 80px;">
                    <i class="bi bi-hdd-network display-5"></i>
                </span>
            </div>
            <span class="badge text-bg-secondary align-self-center mb-2 px-3 py-1">ERROR 500 — SERVER ERROR</span>
            <h1 class="h3 fw-bold text-dark mb-2">Terjadi Gangguan pada Server</h1>
            <p class="text-muted mb-4">
                Maaf, terjadi kesalahan teknis saat memproses permintaan Anda. Silakan muat ulang halaman beberapa saat lagi.
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="{{ url()->previous() }}" class="btn btn-primary px-4">
                    <i class="bi bi-arrow-clockwise me-1"></i>Coba Lagi
                </a>
                <a href="{{ url('/') }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-house-door me-1"></i>Ke Halaman Utama
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', '404 Halaman Tidak Ditemukan')
@section('hide_sidebar', true)

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-7 col-lg-6 text-center">
        <div class="card shadow-sm border-0 p-4 p-md-5">
            <div class="mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-warning" style="width: 80px; height: 80px;">
                    <i class="bi bi-compass display-5"></i>
                </span>
            </div>
            <span class="badge text-bg-warning text-dark align-self-center mb-2 px-3 py-1">ERROR 404 — NOT FOUND</span>
            <h1 class="h3 fw-bold text-dark mb-2">Halaman Tidak Ditemukan</h1>
            <p class="text-muted mb-4">
                Maaf, halaman atau data pesanan/jasa yang Anda cari tidak ditemukan atau telah dipindahkan.
            </p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">
                        <i class="bi bi-speedometer2 me-1"></i>Ke Dashboard Saya
                    </a>
                @endauth
                <a href="{{ url('/') }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-house-door me-1"></i>Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', '403 Akses Ditolak')
@section('hide_sidebar', true)

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-7 col-lg-6 text-center">
        <div class="card shadow-sm border-0 p-4 p-md-5">
            <div class="mb-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-danger bg-opacity-10 text-danger" style="width: 80px; height: 80px;">
                    <i class="bi bi-shield-lock-fill display-5"></i>
                </span>
            </div>
            <span class="badge text-bg-danger align-self-center mb-2 px-3 py-1">ERROR 403 — FORBIDDEN</span>
            <h1 class="h3 fw-bold text-dark mb-2">Akses Ditolak</h1>
            <p class="text-muted mb-4">
                {{ $exception->getMessage() ?: 'Maaf, akun Anda tidak memiliki izin peran (role) yang sesuai untuk mengakses halaman ini.' }}
            </p>

            @auth
                <div class="alert alert-light border small text-start mb-4">
                    <i class="bi bi-person-badge me-1 text-primary"></i>
                    Anda saat ini login sebagai <strong>{{ auth()->user()->name }}</strong> dengan peran
                    <span class="badge text-bg-secondary">{{ auth()->user()->role }}</span>.
                </div>
            @endauth

            <div class="d-flex justify-content-center gap-2 flex-wrap">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary px-4">
                        <i class="bi bi-speedometer2 me-1"></i>Kembali ke Dashboard Saya
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary px-4">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login Akun
                    </a>
                @endauth
                <a href="{{ url('/') }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-house-door me-1"></i>Halaman Utama
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

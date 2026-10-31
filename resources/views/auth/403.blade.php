@extends('layouts.app')

@section('title', 'Akses Ditolak')
@section('hide_sidebar', true)

@section('content')
<div class="container text-center py-5" style="max-width: 560px;">
    <i class="bi bi-shield-lock text-danger" style="font-size: 4rem;"></i>
    <h1 class="h3 fw-bold mt-3">403 - Akses Ditolak</h1>
    <p class="text-muted">
        {{ $exception->getMessage() ?: 'Kamu tidak punya izin untuk membuka halaman ini.' }}
    </p>
    <div class="d-flex justify-content-center gap-2 mt-4">
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="bi bi-speedometer2 me-1"></i>Ke Dashboard Saya
            </a>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
        @endauth
        <a href="{{ url('/') }}" class="btn btn-outline-secondary">Beranda</a>
    </div>
</div>
@endsection
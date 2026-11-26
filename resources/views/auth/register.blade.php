@extends('layouts.auth')

@section('title', 'Daftar')

@section('content')
@php $selectedRole = old('role', request('role')); @endphp

<h1 class="h3 fw-bold mb-1">Buat akun baru</h1>
<p class="text-secondary mb-4">Pendaftaran gratis untuk pembeli maupun penjual.</p>

<form method="POST" action="{{ route('register') }}" novalidate>
    @csrf

    <label class="form-label small fw-semibold">Daftar sebagai</label>
    <div class="row g-2 mb-3">
        <div class="col-6">
            <input type="radio" class="btn-check" name="role" id="role-buyer" value="buyer" @checked($selectedRole === 'buyer')>
            <label class="btn btn-outline-primary w-100 py-2" for="role-buyer">
                <i class="bi bi-bag me-1"></i>Pembeli
            </label>
        </div>
        <div class="col-6">
            <input type="radio" class="btn-check" name="role" id="role-seller" value="seller" @checked($selectedRole === 'seller')>
            <label class="btn btn-outline-primary w-100 py-2" for="role-seller">
                <i class="bi bi-briefcase me-1"></i>Penjual Jasa
            </label>
        </div>
    </div>
    @error('role')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

    <div class="mb-3">
        <label for="name" class="form-label small fw-semibold">Nama lengkap</label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
               value="{{ old('email') }}" placeholder="nama@gmail.com" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label for="password" class="form-label small fw-semibold">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @else
                <div class="form-text">Minimal 8 karakter.</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label for="password_confirmation" class="form-label small fw-semibold">Konfirmasi</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100">Daftar</button>
</form>

<p class="text-center small text-secondary mt-4 mb-0">
    Sudah punya akun? <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Masuk</a>
</p>
@endsection

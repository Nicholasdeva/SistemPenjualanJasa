@extends('layouts.app')

@section('title', 'Daftar')
@section('hide_sidebar', true)

@section('content')
@php $selectedRole = old('role', request('role')); @endphp

<div class="auth-wrap">
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <span class="brand-mark mb-4"><i class="bi bi-briefcase-fill"></i></span>
            <h1 class="h4 fw-bold mb-1">Buat akun baru</h1>
            <p class="text-secondary small mb-4">Pendaftaran gratis untuk pembeli maupun penjual.</p>

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
                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="form-text">Minimal 8 karakter.</div>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label small fw-semibold">Konfirmasi password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Daftar</button>
            </form>
        </div>
        <div class="card-footer bg-white text-center small py-3">
            Sudah punya akun? <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">Masuk</a>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Login')
@section('hide_sidebar', true)

@section('content')
<div class="auth-wrap">
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <span class="brand-mark mb-4"><i class="bi bi-briefcase-fill"></i></span>
            <h1 class="h4 fw-bold mb-1">Masuk ke akun</h1>
            <p class="text-secondary small mb-4">Gunakan email dan password yang sudah terdaftar.</p>

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                           value="{{ old('email') }}" placeholder="nama@gmail.com" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="form-label small fw-semibold">Password</label>
                        <a href="{{ route('password.request') }}" class="small text-decoration-none">Lupa password?</a>
                    </div>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check mb-4">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" @checked(old('remember'))>
                    <label class="form-check-label small" for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>
        </div>
        <div class="card-footer bg-white text-center small py-3">
            Belum punya akun? <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">Daftar</a>
        </div>
    </div>
</div>
@endsection

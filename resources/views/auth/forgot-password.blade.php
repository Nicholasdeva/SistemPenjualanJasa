@extends('layouts.app')

@section('title', 'Lupa Password')
@section('hide_sidebar', true)

@section('content')
<div class="auth-wrap">
    <div class="card">
        <div class="card-body p-4 p-md-5">
            <span class="brand-mark mb-4"><i class="bi bi-briefcase-fill"></i></span>
            <h1 class="h4 fw-bold mb-1">Lupa password</h1>
            <p class="text-secondary small mb-4">Masukkan email akun kamu. Kami akan mengirim tautan untuk mengatur ulang password.</p>

            <form method="POST" action="{{ route('password.email') }}" novalidate>
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label small fw-semibold">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                           value="{{ old('email') }}" placeholder="nama@gmail.com" required autofocus>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-100">Kirim tautan reset</button>
            </form>
        </div>
        <div class="card-footer bg-white text-center small py-3">
            <a href="{{ route('login') }}" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke halaman masuk</a>
        </div>
    </div>
</div>
@endsection

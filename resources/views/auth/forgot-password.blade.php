@extends('layouts.auth')

@section('title', 'Lupa Password')

@section('content')
<h1 class="h3 fw-bold mb-1">Lupa password</h1>
<p class="text-secondary mb-4">Masukkan email akun kamu. Kami akan mengirim tautan untuk mengatur ulang password.</p>

<form method="POST" action="{{ route('password.email') }}" novalidate>
    @csrf
    <div class="mb-4">
        <label for="email" class="form-label small fw-semibold">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
               value="{{ old('email') }}" placeholder="nama@gmail.com" required autofocus>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100">Kirim tautan reset</button>
</form>

<p class="text-center small mt-4 mb-0">
    <a href="{{ route('login') }}" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke halaman masuk</a>
</p>
@endsection

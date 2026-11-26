@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<h1 class="h3 fw-bold mb-1">Atur ulang password</h1>
<p class="text-secondary mb-4">Buat password baru untuk akun kamu.</p>

<form method="POST" action="{{ route('password.update') }}" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Email</label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
               value="{{ old('email', $email) }}" required>
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold">Password baru</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="mb-4">
        <label for="password_confirmation" class="form-label small fw-semibold">Konfirmasi password baru</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>
    <button type="submit" class="btn btn-primary btn-lg w-100">Simpan password</button>
</form>
@endsection

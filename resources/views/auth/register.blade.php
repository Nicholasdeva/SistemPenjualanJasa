@extends('layouts.app')

@section('title', 'Daftar')

@section('content')
<div class="container mt-5" style="max-width: 520px;">
    <h2 class="mb-4">Daftar Akun</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Daftar sebagai</label>
            <select class="form-select" id="role" name="role" required>
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih peran</option>
                <option value="buyer" {{ old('role') === 'buyer' ? 'selected' : '' }}>Pembeli</option>
                <option value="seller" {{ old('role') === 'seller' ? 'selected' : '' }}>Penjual Jasa</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Daftar</button>
        <p class="text-center small mt-3 mb-0">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Daftar Akun')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="text-center mb-2">Buat Akun</h2>
                    <p class="text-center text-muted mb-4">
                        Bergabung dengan Marketplace Jasa
                    </p>

                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama lengkap</label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                class="form-control @error('name') is-invalid @enderror"
                                required
                                maxlength="255"
                                autocomplete="name"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                required
                                autocomplete="email"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Daftar sebagai</label>
                            <select
                                name="role"
                                id="role"
                                class="form-select @error('role') is-invalid @enderror"
                                required
                            >
                                <option value="">Pilih peran</option>
                                <option value="buyer" @selected(old('role') === 'buyer')>
                                    Pembeli
                                </option>
                                <option value="seller" @selected(old('role') === 'seller')>
                                    Penjual Jasa
                                </option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                                autocomplete="new-password"
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimal 8 karakter.</small>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">
                                Konfirmasi password
                            </label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control"
                                required
                                autocomplete="new-password"
                            >
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Daftar Sekarang
                        </button>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        Sudah punya akun?
                        <a href="{{ route('login') }}">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

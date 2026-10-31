@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-4">Atur Ulang Password</h2>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul></div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $email) }}" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Ubah Password</button>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('title', 'Lupa Password')

@section('content')
<div class="container mt-5" style="max-width: 480px;">
    <h2 class="mb-3">Lupa Password</h2>
    <p class="text-muted">Masukkan email akunmu, kami kirimkan tautan untuk mengatur ulang password.</p>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul></div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100">Kirim Tautan Reset</button>
        <p class="text-center small mt-3 mb-0"><a href="{{ route('login') }}">Kembali ke login</a></p>
    </form>
</div>
@endsection
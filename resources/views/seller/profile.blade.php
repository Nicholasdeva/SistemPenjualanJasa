@extends('layouts.app')

@section('content')
<h1 class="h3 mb-4">Profil Penjual</h1>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('seller.profile.update') }}" method="POST" class="col-md-7 p-0">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Nomor KTP</label>
        <input type="text" name="ktp_number" class="form-control" value="{{ old('ktp_number', $profile->ktp_number) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Nama Bank</label>
        <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $profile->bank_name) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Nomor Rekening</label>
        <input type="text" name="bank_account" class="form-control" value="{{ old('bank_account', $profile->bank_account) }}">
    </div>
    <div class="mb-4">
        <label class="form-label d-block">Status Verifikasi</label>
        @if ($profile->is_verified)
            <span class="badge text-bg-success">Terverifikasi</span>
        @else
            <span class="badge text-bg-warning">Menunggu verifikasi admin</span>
        @endif
    </div>

    <button class="btn btn-primary">Simpan Profil</button>
</form>
@endsection
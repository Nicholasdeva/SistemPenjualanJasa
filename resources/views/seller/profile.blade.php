@extends('layouts.app')

@section('title', 'Profil Penjual')

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Profil Penjual</h1>
    <p class="text-secondary small mb-0">Data KTP dan rekening dipakai admin untuk verifikasi dan pencairan saldo.</p>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <form action="{{ route('seller.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="ktp_number" class="form-label small fw-semibold">Nomor KTP</label>
                        <input type="text" id="ktp_number" name="ktp_number" class="form-control @error('ktp_number') is-invalid @enderror"
                               value="{{ old('ktp_number', $profile->ktp_number) }}" placeholder="16 digit NIK" inputmode="numeric" maxlength="20" autocomplete="off">
                        @error('ktp_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <label for="bank_name" class="form-label small fw-semibold">Nama bank</label>
                            <input type="text" id="bank_name" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror"
                                   value="{{ old('bank_name', $profile->bank_name) }}" placeholder="Contoh: BCA">
                            @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-7">
                            <label for="bank_account" class="form-label small fw-semibold">Nomor rekening</label>
                            <input type="text" id="bank_account" name="bank_account" class="form-control @error('bank_account') is-invalid @enderror"
                                   value="{{ old('bank_account', $profile->bank_account) }}" inputmode="numeric" maxlength="25" autocomplete="off">
                            @error('bank_account')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    @if ($profile->is_verified)
                        <p class="small text-secondary">Mengubah nomor KTP atau rekening akan membuat akunmu perlu diverifikasi ulang oleh admin.</p>
                    @endif
                    <button class="btn btn-primary">Simpan Profil</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-body p-4">
                <div class="small text-secondary mb-2">Status verifikasi</div>
                @if ($profile->is_verified)
                    <div class="d-flex align-items-center gap-2 text-success fw-semibold mb-2">
                        <i class="bi bi-patch-check-fill"></i>Terverifikasi
                    </div>
                    <p class="small text-secondary mb-0">Jasa kamu bisa tampil di katalog.</p>
                @else
                    <div class="d-flex align-items-center gap-2 text-warning-emphasis fw-semibold mb-2">
                        <i class="bi bi-clock-history"></i>Menunggu verifikasi admin
                    </div>
                    <p class="small text-secondary mb-0">Pastikan nomor KTP dan rekening sudah diisi dengan benar agar admin bisa memverifikasi akun kamu.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

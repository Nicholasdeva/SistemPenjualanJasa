@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Edit Profil</h1>
    <p class="text-secondary small mb-0">Perbarui nama, password, dan foto profil akun Anda.</p>
</div>

<div class="card">
    <div class="card-body p-4">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label fw-semibold">Foto profil</label>
                <div class="d-flex align-items-center gap-3">
                    @if ($user->avatar_url)
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->avatar_url) }}"
                             alt="Foto profil {{ $user->name }}" class="rounded-circle object-fit-cover border"
                             style="width: 72px; height: 72px;">
                    @else
                        <span class="avatar-circle fs-4">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    @endif
                    <div class="flex-grow-1">
                        <input type="file" name="avatar" id="avatar"
                               class="form-control @error('avatar') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png,.webp">
                        <div class="form-text">JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.</div>
                        @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Nama pengguna</label>
                <input type="text" name="name" id="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" maxlength="255" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <hr class="my-4">
            <h2 class="h6 fw-semibold mb-3">Ubah password <span class="text-secondary fw-normal">(opsional)</span></h2>

            <div class="mb-3">
                <label for="password" class="form-label">Password baru</label>
                <input type="password" name="password" id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       autocomplete="new-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Konfirmasi password baru</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control" autocomplete="new-password">
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4">Simpan Profil</button>
            </div>
        </form>
    </div>
</div>
@endsection

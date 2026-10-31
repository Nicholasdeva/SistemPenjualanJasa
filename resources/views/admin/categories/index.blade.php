@extends('layouts.app')

@section('title', 'Kelola Kategori')

@section('content')
<h1 class="h4 fw-bold mb-4">Kelola Kategori</h1>

@if ($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">
        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul></div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="row g-2">
            @csrf
            <div class="col-12 col-md">
                <input type="text" name="name" class="form-control" placeholder="Nama kategori baru" required>
            </div>
            <div class="col-12 col-md-auto">
                <button class="btn btn-primary w-100">Tambah</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>Slug</th>
                    <th>Jumlah Jasa</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td style="min-width: 220px;">
                            <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="d-flex gap-2">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $category->name }}" class="form-control form-control-sm" required>
                                <button class="btn btn-sm btn-outline-primary">Simpan</button>
                            </form>
                        </td>
                        <td class="text-muted">{{ $category->slug }}</td>
                        <td>{{ $counts[$category->id] ?? 0 }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                  class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
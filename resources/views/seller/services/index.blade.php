@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Daftar Jasa Saya</h1>
    <a href="{{ route('seller.services.create') }}" class="btn btn-primary">Tambah Jasa</a>
</div>

@if ($services->isEmpty())
    <div class="alert alert-secondary">Belum ada jasa.</div>
@else
    <div class="table-responsive">
        <table class="table table-bordered bg-white align-middle">
            <thead class="table-light">
                <tr>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Paket</th>
                    <th>Status</th>
                    <th style="width:170px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($services as $service)
                <tr>
                    <td>{{ $service->title }}</td>
                    <td>{{ $service->category->name }}</td>
                    <td>{{ $service->packages->count() }} paket</td>
                    <td>
                        <span class="badge {{ $service->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">
                            {{ $service->status === 'published' ? 'Tayang' : 'Draft' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('seller.services.edit', $service) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('seller.services.destroy', $service) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus jasa ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
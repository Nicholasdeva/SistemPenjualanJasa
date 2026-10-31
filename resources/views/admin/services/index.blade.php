@extends('layouts.app')

@section('title', 'Moderasi Layanan')

@section('content')
<h1 class="h4 fw-bold mb-4">Moderasi Layanan</h1>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Judul</th>
                    <th>Penjual</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td class="fw-semibold">{{ $service->title }}</td>
                        <td>{{ $sellers[$service->seller_id] ?? '-' }}</td>
                        <td>{{ $categories[$service->category_id] ?? '-' }}</td>
                        <td>
                            @if ($service->status === 'published')
                                <span class="badge text-bg-success">Tayang</span>
                            @else
                                <span class="badge text-bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($service->status === 'published')
                                <form method="POST" action="{{ route('admin.services.deactivate', $service) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-danger">Turunkan</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.services.activate', $service) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-success">Tayangkan</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada jasa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
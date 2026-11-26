@extends('layouts.app')

@section('title', 'Moderasi Layanan')

@section('content')
<div class="mb-4">
    <h1 class="h4 fw-bold mb-1">Moderasi Layanan</h1>
    <p class="text-secondary small mb-0">Turunkan jasa yang melanggar aturan atau tayangkan kembali.</p>
</div>

<div class="card">
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
                        <td>{{ $service->seller?->name ?? '-' }}</td>
                        <td>{{ $service->category?->name ?? '-' }}</td>
                        <td>
                            @if ($service->status === \App\Models\Service::STATUS_PUBLISHED)
                                <span class="badge text-bg-success">Tayang</span>
                            @elseif ($service->status === \App\Models\Service::STATUS_DRAFT)
                                <span class="badge text-bg-secondary">Draft</span>
                            @else
                                <span class="badge text-bg-warning">{{ ucfirst($service->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if ($service->status === \App\Models\Service::STATUS_PUBLISHED)
                                <form method="POST" action="{{ route('admin.services.deactivate', $service) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-danger">Turunkan</button>
                                </form>
                            @elseif ($service->status === \App\Models\Service::STATUS_DRAFT)
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
    <div class="card-footer bg-white">
        {{ $services->links() }}
    </div>
</div>
@endsection
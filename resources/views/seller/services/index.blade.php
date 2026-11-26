@extends('layouts.app')

@section('title', 'Jasa Saya')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1">Jasa Saya</h1>
        <p class="text-secondary small mb-0">Kelola jasa dan paket yang kamu tawarkan.</p>
    </div>
    <a href="{{ route('seller.services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah Jasa</a>
</div>

@unless ($isVerified)
    <div class="alert alert-warning small d-flex gap-2">
        <i class="bi bi-exclamation-circle"></i>
        <div>
            Akunmu belum diverifikasi admin, jadi jasa berstatus tayang belum muncul di katalog.
            <a href="{{ route('seller.profile.edit') }}" class="alert-link">Lengkapi profil</a>
        </div>
    </div>
@endunless

@if ($services->isEmpty())
    <x-empty-state
        title="Belum ada jasa"
        description="Tambahkan jasa pertama kamu beserta paket Basic, Standard, dan Premium."
        icon="bi-briefcase"
        actionText="Tambah Jasa"
        :actionUrl="route('seller.services.create')"
    />
@else
    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Jasa</th>
                        <th>Kategori</th>
                        <th>Harga mulai</th>
                        <th>Pesanan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-2 border bg-light flex-shrink-0 overflow-hidden d-flex align-items-center justify-content-center text-secondary"
                                         style="width: 64px; height: 40px;">
                                        @if ($service->thumbnail)
                                            <img src="{{ $service->thumbnail }}" alt="" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <i class="bi bi-image"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $service->title }}</div>
                                        <div class="small text-secondary">{{ $service->packages->pluck('tier_name')->implode(', ') ?: 'Belum ada paket' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $service->category->name ?? '-' }}</td>
                            <td class="text-nowrap">
                                @php $min = $service->packages->min('price'); @endphp
                                {{ $min !== null ? 'Rp' . number_format($min, 0, ',', '.') : '-' }}
                            </td>
                            <td>{{ $service->orders_count }}</td>
                            <td>
                                @if ($service->status === 'published')
                                    <span class="badge text-bg-success">Tayang</span>
                                @else
                                    <span class="badge text-bg-secondary">Draft</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                @if ($service->status === 'published' && $isVerified)
                                    <a href="{{ route('catalog.show', $service) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Lihat di katalog">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                @endif
                                <a href="{{ route('seller.services.edit', $service) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                @if ($service->orders_count === 0)
                                    <form action="{{ route('seller.services.destroy', $service) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Hapus jasa ini? Tindakan ini tidak bisa dibatalkan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <p class="small text-secondary mt-2 mb-0">Jasa yang sudah pernah dipesan tidak bisa dihapus. Ubah statusnya ke draft untuk menyembunyikannya.</p>
@endif
@endsection

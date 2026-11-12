{{-- Kartu jasa untuk beranda dan katalog. Butuh: $service (dengan seller, category, packages terurut harga, reviews_avg_rating, reviews_count) --}}
@php
    $cheapest = $service->packages->first();
    $sellerName = $service->seller->name ?? 'Penjual';
    $catName = $service->category->name ?? 'Lainnya';
@endphp

@once
    @push('styles')
    <style>
        .svc-card { text-decoration: none; color: inherit; transition: border-color .18s ease; }
        .svc-card:hover { color: inherit; border-color: #2563eb; }
        .svc-card .svc-title { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.7em; line-height: 1.35; color: #0f172a; }
        .svc-card .svc-desc { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .svc-card .svc-seller { width: 26px; height: 26px; border-radius: 50%; background: #dfe5ee; color: #334155; font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .svc-card .svc-price { font-weight: 600; color: #0f172a; }
        .svc-card .svc-arrow { color: #2563eb; opacity: 0; transform: translateX(-4px); transition: all .18s ease; }
        .svc-card:hover .svc-arrow { opacity: 1; transform: none; }
    </style>
    @endpush
@endonce

<a href="{{ route('catalog.show', $service) }}" class="card svc-card h-100 overflow-hidden">
    @if ($service->thumbnail)
        <div class="ratio ratio-16x9 border-bottom bg-light">
            <img src="{{ $service->thumbnail }}" alt="{{ $service->title }}" class="object-fit-cover" loading="lazy">
        </div>
    @endif
    <div class="card-body p-4 d-flex flex-column">
        <div class="d-flex align-items-center gap-2 mb-3">
            <span class="icon-tile {{ \App\Support\CategoryStyle::tone($catName) }}" style="width:34px;height:34px;font-size:.95rem;">
                <i class="bi {{ \App\Support\CategoryStyle::icon($catName) }}"></i>
            </span>
            <span class="small fw-semibold text-secondary">{{ $catName }}</span>
            @if ($service->reviews_count > 0)
                <span class="ms-auto small fw-semibold text-dark text-nowrap">
                    <i class="bi bi-star-fill text-warning"></i>
                    {{ number_format($service->reviews_avg_rating, 1, ',', '') }}
                    <span class="text-secondary fw-normal">({{ $service->reviews_count }})</span>
                </span>
            @endif
        </div>

        <h3 class="h6 fw-bold svc-title mb-2">{{ $service->title }}</h3>
        <p class="small text-secondary svc-desc mb-4">{{ \Illuminate\Support\Str::limit(strip_tags($service->description), 120) }}</p>

        <div class="d-flex align-items-center gap-2 mt-auto pt-3 border-top">
            <span class="svc-seller">{{ strtoupper(mb_substr($sellerName, 0, 1)) }}</span>
            <span class="small text-truncate">{{ $sellerName }}</span>
            @if ($cheapest)
                <span class="ms-auto text-end lh-sm">
                    <span class="d-block text-secondary" style="font-size:.7rem;">Mulai dari</span>
                    <span class="svc-price">Rp{{ number_format((float) $cheapest->price, 0, ',', '.') }}</span>
                </span>
            @endif
            <i class="bi bi-arrow-right svc-arrow"></i>
        </div>
    </div>
</a>

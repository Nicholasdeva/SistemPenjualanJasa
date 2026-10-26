@props([
    'title' => 'Belum Ada Data',
    'description' => 'Data yang Anda cari belum tersedia saat ini.',
    'icon' => 'bi-inbox',
    'actionText' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-5 px-3 bg-white rounded-3 border border-dashed']) }}>
    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light text-secondary mb-3" style="width: 68px; height: 68px;">
        <i class="bi {{ $icon }} fs-2"></i>
    </div>
    <h5 class="fw-semibold text-dark mb-1">{{ $title }}</h5>
    <p class="text-muted small mx-auto mb-3" style="max-width: 420px;">{{ $description }}</p>

    @if ($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i>{{ $actionText }}
        </a>
    @endif

    {{ $slot }}
</div>

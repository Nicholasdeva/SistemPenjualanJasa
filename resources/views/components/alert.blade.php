@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => true,
    'icon' => null,
])

@php
    $normalizedType = match($type) {
        'error' => 'danger',
        default => $type,
    };

    $defaultIcon = $icon ?? match($normalizedType) {
        'success' => 'bi-check-circle-fill',
        'danger'  => 'bi-exclamation-triangle-fill',
        'warning' => 'bi-exclamation-circle-fill',
        default   => 'bi-info-circle-fill',
    };
@endphp

<div {{ $attributes->merge(['class' => "alert alert-{$normalizedType} " . ($dismissible ? 'alert-dismissible fade show ' : '') . 'd-flex align-items-start gap-2 shadow-sm']) }} role="alert">
    <i class="bi {{ $defaultIcon }} fs-5 flex-shrink-0 mt-1"></i>
    <div class="flex-grow-1">
        @if ($title)
            <div class="fw-bold mb-1">{{ $title }}</div>
        @endif
        <div>{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    @endif
</div>

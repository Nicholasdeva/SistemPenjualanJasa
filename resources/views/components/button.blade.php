@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => null,
    'href' => null,
    'icon' => null,
    'outline' => false,
    'disabled' => false,
])

@php
    $btnStyle = $outline ? "btn-outline-{$variant}" : "btn-{$variant}";
    $sizeClass = $size ? "btn-{$size}" : '';
    $classes = trim("btn {$btnStyle} {$sizeClass} d-inline-flex align-items-center justify-content-center gap-1");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes . ($disabled ? ' disabled' : '')]) }}>
        @if ($icon)
            <i class="bi {{ $icon }}"></i>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)
            <i class="bi {{ $icon }}"></i>
        @endif
        <span>{{ $slot }}</span>
    </button>
@endif

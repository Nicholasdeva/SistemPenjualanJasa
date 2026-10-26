@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'headerAction' => null,
    'footer' => null,
    'bodyClass' => '',
    'headerClass' => 'bg-white py-3',
])

<div {{ $attributes->merge(['class' => 'card shadow-sm']) }}>
    @if ($title || $headerAction)
        <div class="card-header {{ $headerClass }} d-flex justify-content-between align-items-center">
            <div>
                @if ($title)
                    <h5 class="card-title mb-0 fw-semibold fs-6 d-flex align-items-center gap-2">
                        @if ($icon)
                            <i class="bi {{ $icon }} text-primary"></i>
                        @endif
                        <span>{{ $title }}</span>
                    </h5>
                @endif
                @if ($subtitle)
                    <div class="text-muted small mt-1">{{ $subtitle }}</div>
                @endif
            </div>
            @if ($headerAction)
                <div>{{ $headerAction }}</div>
            @endif
        </div>
    @endif

    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="card-footer bg-light border-top-0 py-2 px-3">
            {{ $footer }}
        </div>
    @endif
</div>

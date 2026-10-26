@props([
    'rating' => 0,
    'max' => 5,
    'interactive' => false,
    'name' => 'rating',
    'showValue' => false,
    'count' => null,
    'size' => 'fs-6',
])

@php
    $numericRating = (float) $rating;
    $uid = 'star_rating_' . uniqid();
@endphp

@if ($interactive)
    <div class="star-rating-input d-inline-flex flex-column gap-2" id="{{ $uid }}">
        <div class="d-inline-flex align-items-center gap-2">
            @for ($i = 1; $i <= $max; $i++)
                <label class="cursor-pointer mb-0" style="cursor: pointer;" data-star-value="{{ $i }}" title="{{ $i }} Bintang">
                    <input type="radio" name="{{ $name }}" value="{{ $i }}" class="d-none" @checked((int) $numericRating === $i)>
                    <i class="bi {{ $i <= $numericRating ? 'bi-star-fill text-warning' : 'bi-star text-secondary' }} fs-2 star-icon transition"></i>
                </label>
            @endfor
            <span class="badge text-bg-warning text-dark ms-2 px-3 py-2 fs-6" data-rating-label>
                {{ (int) $numericRating ?: 5 }} / {{ $max }} Bintang
            </span>
        </div>
        <div class="text-muted small" data-rating-caption>
            Klik pada bintang untuk memilih penilaian Anda (1 = Sangat Kurang, 5 = Sangat Memuaskan).
        </div>
    </div>

    <script>
        (function () {
            const container = document.getElementById('{{ $uid }}');
            if (!container) return;
            const labels = container.querySelectorAll('label[data-star-value]');
            const badge = container.querySelector('[data-rating-label]');
            const caption = container.querySelector('[data-rating-caption]');
            const descriptions = {
                1: '1 Bintang — Sangat Kurang Memuaskan',
                2: '2 Bintang — Kurang Memuaskan',
                3: '3 Bintang — Cukup Sesuai Ekspektasi',
                4: '4 Bintang — Bagus & Direkomendasikan',
                5: '5 Bintang — Sangat Memuaskan & Profesional!'
            };

            function renderStars(val) {
                labels.forEach(lbl => {
                    const starVal = parseInt(lbl.getAttribute('data-star-value'), 10);
                    const icon = lbl.querySelector('.star-icon');
                    if (starVal <= val) {
                        icon.classList.remove('bi-star', 'text-secondary');
                        icon.classList.add('bi-star-fill', 'text-warning');
                    } else {
                        icon.classList.remove('bi-star-fill', 'text-warning');
                        icon.classList.add('bi-star', 'text-secondary');
                    }
                });
                if (badge) badge.textContent = val + ' / {{ $max }} Bintang';
                if (caption && descriptions[val]) caption.textContent = descriptions[val];
            }

            labels.forEach(lbl => {
                lbl.addEventListener('click', () => {
                    const val = parseInt(lbl.getAttribute('data-star-value'), 10);
                    const radio = lbl.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                    renderStars(val);
                });
            });
        })();
    </script>
@else
    <span {{ $attributes->merge(['class' => 'd-inline-flex align-items-center gap-1']) }}>
        @for ($i = 1; $i <= $max; $i++)
            @if ($numericRating >= $i)
                <i class="bi bi-star-fill text-warning {{ $size }}"></i>
            @elseif ($numericRating >= ($i - 0.5))
                <i class="bi bi-star-half text-warning {{ $size }}"></i>
            @else
                <i class="bi bi-star text-muted opacity-50 {{ $size }}"></i>
            @endif
        @endfor

        @if ($showValue)
            <span class="fw-semibold text-dark ms-1 small">{{ number_format($numericRating, 1) }}</span>
        @endif

        @if (!is_null($count))
            <span class="text-muted small">({{ $count }})</span>
        @endif
    </span>
@endif

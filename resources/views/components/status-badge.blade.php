@props([
    'status' => 'pending',
])

@php
    $normalized = strtolower(trim((string) $status));

    $config = match($normalized) {
        'pending', 'pending_payment' => [
            'label' => 'Menunggu Pembayaran',
            'class' => 'text-bg-warning text-dark',
            'icon'  => 'bi-clock-history',
        ],
        'paid' => [
            'label' => 'Dibayar (Escrow)',
            'class' => 'text-bg-info text-dark',
            'icon'  => 'bi-shield-lock-fill',
        ],
        'in_progress' => [
            'label' => 'Sedang Dikerjakan',
            'class' => 'text-bg-primary',
            'icon'  => 'bi-gear-wide-connected',
        ],
        'delivered' => [
            'label' => 'Hasil Dikirim',
            'class' => 'bg-indigo text-white',
            'style' => 'background-color: #6366f1;',
            'icon'  => 'bi-box-seam-fill',
        ],
        'revision' => [
            'label' => 'Permintaan Revisi',
            'class' => 'text-bg-warning text-dark',
            'icon'  => 'bi-arrow-repeat',
        ],
        'completed' => [
            'label' => 'Selesai',
            'class' => 'text-bg-success',
            'icon'  => 'bi-check-circle-fill',
        ],
        'disputed' => [
            'label' => 'Dalam Sengketa',
            'class' => 'text-bg-danger',
            'icon'  => 'bi-exclamation-octagon-fill',
        ],
        'cancelled' => [
            'label' => 'Dibatalkan',
            'class' => 'text-bg-secondary',
            'icon'  => 'bi-x-circle-fill',
        ],
        'published' => [
            'label' => 'Tayang',
            'class' => 'text-bg-success',
            'icon'  => 'bi-globe2',
        ],
        'draft' => [
            'label' => 'Draft',
            'class' => 'text-bg-secondary',
            'icon'  => 'bi-file-earmark-text',
        ],
        'approved' => [
            'label' => 'Disetujui',
            'class' => 'text-bg-success',
            'icon'  => 'bi-check2-circle',
        ],
        'rejected' => [
            'label' => 'Ditolak',
            'class' => 'text-bg-danger',
            'icon'  => 'bi-x-octagon',
        ],
        default => [
            'label' => ucfirst(str_replace('_', ' ', $normalized)),
            'class' => 'text-bg-secondary',
            'icon'  => 'bi-tag-fill',
        ],
    };
@endphp

<span {{ $attributes->merge([
    'class' => 'badge rounded-pill d-inline-flex align-items-center gap-1 px-2 py-1 fw-medium ' . $config['class'],
    'style' => $config['style'] ?? null,
]) }}>
    <i class="bi {{ $config['icon'] }}"></i>
    <span>{{ $config['label'] }}</span>
</span>

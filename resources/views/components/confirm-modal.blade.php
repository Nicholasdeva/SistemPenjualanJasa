@props([
    'id',
    'title' => 'Konfirmasi Tindakan',
    'message' => 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    'action' => '#',
    'method' => 'POST',
    'confirmText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
    'variant' => 'danger',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ $action }}" method="POST">
                @csrf
                @if (!in_array(strtoupper($method), ['GET', 'POST']))
                    @method($method)
                @endif

                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="{{ $id }}Label">
                        <i class="bi bi-question-circle-fill text-{{ $variant }}"></i>
                        <span>{{ $title }}</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body py-3">
                    <p class="text-secondary mb-2">{{ $message }}</p>
                    {{ $slot }}
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">{{ $cancelText }}</button>
                    <button type="submit" class="btn btn-{{ $variant }}">{{ $confirmText }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@extends('layouts.app')

@section('title', 'Workspace Pesanan ' . $order->invoice_no)

@section('content')
@php
    $service = $order->servicePackage?->service;
    $seller = $service?->seller;
    $buyer = $order->buyer;
    $tierName = $order->servicePackage?->tier_name ?? '-';
    $serviceTitle = $service?->title ?? 'Pesanan Jasa';
    $isOverdue = $order->isOverdue();
    $isFinished = in_array($order->status, ['completed', 'cancelled'], true);
    $revPercent = $revisionLimit > 0
        ? min(100, (int) round(($revisionUsed / $revisionLimit) * 100))
        : 100;
@endphp

<!-- Header Workspace -->
<div class="card mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                    <span class="badge text-bg-dark">{{ $order->invoice_no }}</span>
                    <span class="badge text-bg-light border">Paket {{ $tierName }}</span>
                    <x-status-badge :status="$order->status" />
                </div>
                <h1 class="h4 fw-bold mb-1">{{ $serviceTitle }}</h1>
                <p class="text-secondary small mb-0 d-flex flex-wrap gap-3">
                    <span><i class="bi bi-person-check me-1"></i>Pembeli: <strong class="text-dark">{{ $buyer?->name ?? '-' }}</strong></span>
                    <span><i class="bi bi-shop me-1"></i>Penjual: <strong class="text-dark">{{ $seller?->name ?? '-' }}</strong></span>
                    <span><i class="bi bi-cash-coin me-1"></i>Nilai Escrow: <strong class="text-success">Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong></span>
                </p>
            </div>
            <div class="d-flex gap-2">
                @if ($isSeller)
                    <x-button :href="route('seller.orders.index')" variant="outline-secondary" size="sm" icon="bi-arrow-left">
                        Ke Pesanan Masuk
                    </x-button>
                @else
                    <x-button :href="route('buyer.orders.index')" variant="outline-secondary" size="sm" icon="bi-arrow-left">
                        Ke Pesanan Saya
                    </x-button>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($errors->any())
    <x-alert type="danger" title="Periksa kembali input Anda:" class="mb-4">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif

@if ($order->status === 'disputed')
    <x-alert type="danger" title="Pesanan Sedang Dalam Sengketa (Dispute)" :dismissible="false" class="mb-4">
        Workspace ini berada dalam mode <strong>baca-saja (read-only)</strong> sambil menunggu keputusan mediasi dari Admin di Dispute Center.
    </x-alert>
@elseif ($order->status === 'completed')
    <x-alert type="success" title="Pesanan Selesai" :dismissible="false" class="mb-4">
        Hasil pekerjaan telah disetujui dan pesanan dinyatakan selesai. Riwayat diskusi dan file tetap dapat diakses sebagai arsip.
    </x-alert>
@elseif ($order->status === 'cancelled')
    <x-alert type="secondary" title="Pesanan Dibatalkan" :dismissible="false" class="mb-4">
        Pesanan ini telah dibatalkan. Workspace bersifat baca-saja.
    </x-alert>
@elseif ($isSeller && $order->status === 'paid')
    <x-alert type="info" title="Pesanan Siap Dikerjakan" :dismissible="false" class="mb-4">
        Pembayaran dari pembeli sudah masuk ke Escrow. Klik <strong>Mulai Kerjakan</strong> di panel kanan agar status berubah ke <code>in_progress</code> dan Anda dapat mengunggah hasil kerja.
    </x-alert>
@endif

<!-- Banner Sinkronisasi Status (muncul otomatis via polling AJAX jika lawan bicara mengubah status pesanan) -->
<div id="workspaceStatusSyncAlert" class="alert alert-info d-none align-items-center justify-content-between mb-4" role="alert">
    <div>
        <i class="bi bi-arrow-clockwise me-1"></i>
        <span id="workspaceStatusSyncText">Status pesanan telah diperbarui.</span>
    </div>
    <button type="button" class="btn btn-sm btn-primary" onclick="window.location.reload()">Muat Ulang Halaman</button>
</div>

<div class="row g-4">
    <!-- Kolom Kiri (8 Kolom): Kartu Brief, Daftar File Hasil, dan Ruang Chat -->
    <div class="col-lg-8">
        <!-- 1. Kartu Brief Proyek -->
        <x-card title="Brief & Spesifikasi Proyek" subtitle="Instruksi resmi dari pembeli saat pemesanan" icon="bi-journal-text" class="mb-4">
            @if (filled($order->project_brief))
                <p class="mb-0 text-dark" style="line-height: 1.65; white-space: pre-line;">{{ $order->project_brief }}</p>
            @else
                <p class="mb-0 text-muted fst-italic">Pembeli tidak menambahkan catatan brief khusus pada pesanan ini.</p>
            @endif
        </x-card>

        <!-- 2. Daftar File Hasil Kerja -->
        <x-card title="Daftar File Hasil Kerja" subtitle="File pengiriman resmi dari penjual untuk pesanan ini" icon="bi-folder-check" class="mb-4">
            @if (empty($deliverables))
                <x-empty-state
                    title="Belum Ada File Hasil"
                    description="Penjual belum mengunggah file hasil pekerjaan untuk pesanan ini."
                    icon="bi-cloud-arrow-up"
                />
            @else
                <div class="list-group list-group-flush">
                    @foreach ($deliverables as $item)
                        <div class="list-group-item px-0 py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-file-earmark-arrow-down text-primary fs-4 lh-1 mt-1"></i>
                                <div>
                                    <div class="d-flex align-items-center flex-wrap gap-2">
                                        <span class="fw-semibold text-dark">{{ $item['filename'] }}</span>
                                        <span class="badge text-bg-light border">{{ $item['version'] }}</span>
                                    </div>
                                    @if ($item['note'] !== '')
                                        <div class="small text-secondary mt-1">{{ $item['note'] }}</div>
                                    @endif
                                    <div class="text-muted" style="font-size: 0.76rem;">
                                        @if ($item['filesize'])
                                            Ukuran: {{ $item['filesize'] }} •
                                        @endif
                                        Diunggah pada {{ $item['uploaded_at'] }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-nowrap">
                                @if ($item['download_url'])
                                    <a href="{{ $item['download_url'] }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>Unduh File
                                    </a>
                                @else
                                    <span class="badge text-bg-light border text-muted">Tanpa berkas</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($canDeliver)
                <div class="mt-3 pt-3 border-top">
                    <form action="{{ route('workspace.deliver', $order->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <h6 class="fw-semibold small text-uppercase text-muted mb-2">
                            <i class="bi bi-cloud-upload me-1"></i>Unggah Hasil Kerja (Khusus Penjual)
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="file"
                                       name="delivery_file"
                                       accept=".jpg,.jpeg,.png,.webp,.pdf,.zip,.docx"
                                       class="form-control form-control-sm @error('delivery_file') is-invalid @enderror"
                                       required>
                                @error('delivery_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <input type="text"
                                       name="delivery_note"
                                       value="{{ old('delivery_note') }}"
                                       class="form-control form-control-sm @error('delivery_note') is-invalid @enderror"
                                       placeholder="Catatan pengiriman hasil kerja..."
                                       required>
                                @error('delivery_note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success btn-sm w-100">Kirim Hasil</button>
                            </div>
                        </div>
                        <div class="form-text mt-1">
                            Format: JPG, PNG, WEBP, PDF, ZIP, DOCX (maks. 10 MB). Mengunggah hasil akan mengubah status pesanan menjadi <code>delivered</code>.
                        </div>
                    </form>
                </div>
            @endif
        </x-card>

        <!-- 3. Ruang Chat Diskusi Pembeli & Penjual -->
        <x-card title="Diskusi Workspace" subtitle="Komunikasi terekam sebagai bukti resmi transaksi Escrow" icon="bi-chat-dots-fill">
            <x-slot:headerAction>
                <span class="badge text-bg-light border text-secondary fw-normal" id="chatPollIndicator">
                    <i class="bi bi-broadcast text-success me-1"></i>Sinkron otomatis
                </span>
            </x-slot:headerAction>

            <div id="workspaceChatContainer"
                 data-messages-url="{{ route('workspace.messages', $order->id) }}"
                 data-last-id="{{ $lastMessageId }}"
                 data-order-status="{{ $order->status }}"
                 data-deliverables-count="{{ count($deliverables) }}"
                 class="d-flex flex-column gap-3 mb-4 pe-1"
                 style="max-height: 460px; overflow-y: auto;">
                @forelse ($messages as $msg)
                    @if ($msg['is_system'])
                        <div class="text-center my-1" data-message-id="{{ $msg['id'] }}">
                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-3 py-2 fw-normal text-wrap lh-base">
                                <i class="bi bi-info-circle me-1"></i>{{ $msg['message'] }} ({{ $msg['time'] }})
                            </span>
                        </div>
                    @else
                        <div class="d-flex flex-column {{ $msg['is_own'] ? 'align-items-end' : 'align-items-start' }}" data-message-id="{{ $msg['id'] }}">
                            <div class="small text-muted mb-1 px-1">
                                <strong>{{ $msg['sender_name'] }}</strong>
                                <span class="badge {{ $msg['sender_role'] === 'seller' ? 'text-bg-success' : 'text-bg-primary' }} ms-1" style="font-size: 0.65rem;">
                                    {{ $msg['sender_role'] === 'seller' ? 'Penjual' : 'Pembeli' }}
                                </span>
                                <span class="ms-1">{{ $msg['time'] }}</span>
                            </div>

                            <div class="p-3 rounded-3 shadow-sm {{ $msg['is_delivery'] ? 'border border-success bg-success bg-opacity-10 text-dark' : ($msg['is_own'] ? 'bg-primary text-white' : 'bg-light border text-dark') }}" style="max-width: 82%;">
                                @if ($msg['is_delivery'])
                                    <div class="fw-bold text-success small mb-1">
                                        <i class="bi bi-box-seam-fill me-1"></i>PENGIRIMAN HASIL KERJA
                                    </div>
                                @endif

                                @if ($msg['message'] !== '')
                                    <div class="mb-0" style="white-space: pre-line;">{{ $msg['message'] }}</div>
                                @endif

                                @if ($msg['attachment_name'])
                                    <div class="mt-2 pt-2 border-top {{ $msg['is_own'] && !$msg['is_delivery'] ? 'border-light border-opacity-25' : '' }} small d-flex align-items-center gap-2">
                                        <i class="bi bi-paperclip"></i>
                                        @if ($msg['attachment_url'])
                                            <a href="{{ $msg['attachment_url'] }}"
                                               class="{{ $msg['is_own'] && !$msg['is_delivery'] ? 'text-white' : 'text-primary' }} text-decoration-underline">
                                                {{ $msg['attachment_name'] }}
                                            </a>
                                        @else
                                            <span>{{ $msg['attachment_name'] }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @empty
                    <div id="emptyChatPlaceholder" class="text-center text-muted py-4 small">
                        <i class="bi bi-chat-square-dots fs-4 d-block mb-1"></i>
                        Belum ada pesan di ruang kerja ini. Mulai diskusi dengan mengirim pesan di bawah.
                    </div>
                @endforelse
            </div>

            <!-- Form Kirim Pesan -->
            @if ($isReadOnly)
                <div class="alert alert-secondary small mb-0 text-center">
                    <i class="bi bi-lock-fill me-1"></i>Form pesan dinonaktifkan karena pesanan berstatus <strong>{{ $order->status }}</strong>.
                </div>
            @else
                <form id="workspaceMessageForm"
                      action="{{ route('workspace.message', $order->id) }}"
                      method="POST"
                      enctype="multipart/form-data"
                      class="border-top pt-3">
                    @csrf
                    <div class="mb-2">
                        <textarea name="message"
                                  id="workspaceMessageInput"
                                  rows="2"
                                  class="form-control @error('message') is-invalid @enderror"
                                  placeholder="Tulis pesan balasan atau instruksi tambahan di sini...">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div id="chatErrorBox" class="alert alert-danger py-2 px-3 small d-none mb-2"></div>
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <label class="btn btn-outline-secondary btn-sm mb-0">
                                <i class="bi bi-paperclip me-1"></i>Lampirkan File
                                <input type="file"
                                       name="attachment"
                                       id="workspaceAttachmentInput"
                                       accept=".jpg,.jpeg,.png,.webp,.pdf,.zip,.docx"
                                       class="d-none">
                            </label>
                            <span id="workspaceAttachmentLabel" class="text-muted small">Maks. 10 MB (JPG, PNG, WEBP, PDF, ZIP, DOCX)</span>
                        </div>
                        <button type="submit" id="workspaceSendBtn" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                            <i class="bi bi-send-fill"></i>
                            <span>Kirim Pesan</span>
                        </button>
                    </div>
                </form>
            @endif
        </x-card>
    </div>

    <!-- Kolom Kanan (4 Kolom): Hitung Mundur Deadline, Kuota Revisi, & Aksi Pesanan -->
    <div class="col-lg-4">
        <!-- 1. Hitung Mundur Deadline -->
        <x-card title="Batas Waktu Pengerjaan" icon="bi-alarm-fill" class="mb-4">
            @if ($isFinished)
                <div class="p-3 rounded-3 text-center border bg-light">
                    <div class="small text-uppercase fw-semibold text-muted mb-1">Status Batas Waktu</div>
                    <div class="fs-5 fw-bold {{ $order->status === 'completed' ? 'text-success' : 'text-secondary' }}">
                        {{ $order->status === 'completed' ? 'SELESAI' : 'DIBATALKAN' }}
                    </div>
                    @if ($order->deadline_at)
                        <div class="small mt-1 text-muted">
                            Target awal: <strong>{{ $order->deadline_at->format('d M Y, H:i') }}</strong>
                        </div>
                    @endif
                </div>
            @elseif (!$order->deadline_at)
                <div class="p-3 rounded-3 text-center border bg-light">
                    <div class="small text-uppercase fw-semibold text-muted mb-1">Hitung Mundur Deadline</div>
                    <div class="fs-6 fw-bold text-secondary">Belum Dimulai</div>
                    <div class="small mt-1 text-muted">
                        Deadline dihitung otomatis setelah pembayaran dikonfirmasi.
                    </div>
                </div>
            @else
                <div id="deadlineCountdownBox"
                     data-deadline="{{ $order->deadline_at->toIso8601String() }}"
                     class="p-3 rounded-3 text-center border {{ $isOverdue ? 'bg-danger bg-opacity-10 border-danger text-danger' : 'bg-light' }}">
                    <div class="small text-uppercase fw-semibold mb-1" id="countdownLabelText">
                        {{ $isOverdue ? 'Melewati Batas Waktu (Overdue)' : 'Hitung Mundur Deadline' }}
                    </div>
                    <div class="fs-4 fw-bold font-monospace" id="countdownTimerText">
                        {{ $isOverdue ? 'TERLEWAT' : '--h : --j : --m : --d' }}
                    </div>
                    <div class="small mt-1 {{ $isOverdue ? 'text-danger' : 'text-muted' }}">
                        Target selesai: <strong>{{ $order->deadline_at->format('d M Y, H:i') }}</strong>
                    </div>
                </div>
            @endif
        </x-card>

        <!-- 2. Sisa Kuota Revisi -->
        <x-card title="Kuota Revisi Paket" icon="bi-arrow-repeat" class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Revisi Terpakai</span>
                <span class="fw-bold">{{ $revisionUsed }} dari {{ $revisionLimit }}x</span>
            </div>
            <div class="progress mb-2" style="height: 10px;">
                <div class="progress-bar {{ $remainingRevisions === 0 ? 'bg-danger' : 'bg-warning' }}"
                     role="progressbar"
                     style="width: {{ $revPercent }}%"></div>
            </div>
            <div class="small {{ $remainingRevisions === 0 ? 'text-danger fw-semibold' : 'text-muted' }}">
                @if ($remainingRevisions > 0)
                    Tersisa <strong>{{ $remainingRevisions }} kuota revisi</strong> untuk pesanan ini.
                @else
                    Kuota revisi telah habis (0 tersisa). Tombol minta revisi dinonaktifkan.
                @endif
            </div>
        </x-card>

        <!-- 3. Panel Keputusan & Aksi Pesanan -->
        <x-card title="Keputusan & Aksi Pesanan" subtitle="Tindakan sesuai peran pada pesanan ini" icon="bi-sliders2-vertical">
            <div class="d-grid gap-2">
                @if ($isBuyer)
                    <!-- Tombol Setujui Hasil (Aktif hanya saat status delivered) -->
                    <button type="button"
                            class="btn btn-success fw-semibold d-flex align-items-center justify-content-center gap-2"
                            data-bs-toggle="modal"
                            data-bs-target="#approveOrderModal"
                            @disabled(!$canApprove)>
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Setujui Hasil &amp; Selesaikan</span>
                    </button>

                    <!-- Tombol Minta Revisi (Aktif hanya saat delivered & kuota revisi tersisa) -->
                    <button type="button"
                            class="btn btn-warning text-dark fw-semibold d-flex align-items-center justify-content-center gap-2"
                            data-bs-toggle="modal"
                            data-bs-target="#requestRevisionModal"
                            @disabled(!$canRequestRevision)>
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Minta Revisi ({{ $remainingRevisions }} tersisa)</span>
                    </button>

                    @if ($order->status === 'completed' && !$order->review)
                        <x-button :href="route('buyer.reviews.create', $order->id)" variant="primary" icon="bi-star-fill">
                            Beri Ulasan Penjual
                        </x-button>
                    @endif
                @elseif ($isSeller)
                    @if ($order->status === 'paid')
                        <form action="{{ route('seller.orders.start', $order->id) }}" method="POST"
                              onsubmit="return confirm('Mulai kerjakan pesanan {{ $order->invoice_no }}?')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-primary fw-semibold w-100 d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-play-circle-fill"></i>
                                <span>Mulai Kerjakan Pesanan</span>
                            </button>
                        </form>
                    @elseif (in_array($order->status, ['in_progress', 'revision'], true))
                        <div class="p-2 rounded bg-light border small text-secondary">
                            <i class="bi bi-upload text-primary me-1"></i>
                            Unggah hasil kerja melalui panel <strong>Daftar File Hasil Kerja</strong> di sebelah kiri agar pembeli dapat meninjau hasilnya.
                        </div>
                    @elseif ($order->status === 'delivered')
                        <div class="p-2 rounded bg-light border small text-secondary">
                            <i class="bi bi-hourglass-split text-primary me-1"></i>
                            Menunggu pembeli meninjau hasil kerja. Anda tetap dapat mengunggah versi perbaikan jika diperlukan.
                        </div>
                    @endif
                @endif

                <hr class="my-2">

                <!-- Tombol Ajukan Sengketa / Dispute -->
                <button type="button"
                        class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#openDisputeModal"
                        @disabled(!$canOpenDispute)>
                    <i class="bi bi-exclamation-octagon"></i>
                    <span>Ajukan Sengketa ke Admin</span>
                </button>
            </div>

            <div class="mt-3 p-2 rounded bg-light small text-muted" style="font-size: 0.76rem;">
                <i class="bi bi-shield-check text-success me-1"></i>
                @if ($isBuyer)
                    Menekan <strong>Setujui Hasil</strong> akan mengubah status pesanan menjadi <code>completed</code>. Pencairan otomatis Escrow terhubung di Tahap 3.
                @else
                    Dana pesanan sebesar <strong>Rp {{ number_format($order->total_price, 0, ',', '.') }}</strong> ditahan aman di Escrow hingga pembeli menyetujui hasil kerja.
                @endif
            </div>
        </x-card>
    </div>
</div>

@if ($isBuyer)
    <!-- Modal 1: Konfirmasi Setujui Hasil -->
    <x-confirm-modal
        id="approveOrderModal"
        title="Setujui Hasil Pekerjaan?"
        message="Dengan menyetujui hasil pekerjaan ini, pesanan akan dinyatakan Selesai (completed) dan tidak dapat diminta revisi lagi."
        :action="route('workspace.approve', $order->id)"
        method="POST"
        confirmText="Ya, Setujui & Selesaikan"
        variant="success"
    />

    <!-- Modal 2: Minta Revisi -->
    <x-confirm-modal
        id="requestRevisionModal"
        title="Ajukan Permintaan Revisi"
        message="Tuliskan bagian yang perlu diperbaiki oleh penjual. Permintaan ini akan menggunakan 1 kuota revisi Anda (tersisa {{ $remainingRevisions }} kuota)."
        :action="route('workspace.revision', $order->id)"
        method="POST"
        confirmText="Kirim Permintaan Revisi"
        variant="warning"
    >
        <div class="mt-2">
            <label class="form-label small fw-semibold">Catatan Detail Revisi <span class="text-danger">*</span></label>
            <textarea name="revision_note" rows="3" class="form-control" placeholder="Contoh: Tolong warna teks pada logo dibuat lebih terang dan ukuran ikon diperbesar sedikit..." required minlength="10"></textarea>
            <div class="form-text">Minimal 10 karakter.</div>
        </div>
    </x-confirm-modal>
@endif

<!-- Modal 3: Ajukan Sengketa -->
<x-confirm-modal
    id="openDisputeModal"
    title="Ajukan Sengketa (Dispute) ke Admin"
    message="Jika terjadi kendala serius yang tidak dapat diselesaikan bersama, Admin akan memediasi berdasarkan riwayat chat dan file hasil di workspace."
    :action="route('workspace.dispute', $order->id)"
    method="POST"
    confirmText="Ajukan Sengketa"
    variant="danger"
>
    <div class="mt-2">
        <label class="form-label small fw-semibold">Alasan Sengketa <span class="text-danger">*</span></label>
        <textarea name="reason" rows="3" class="form-control" placeholder="Jelaskan ketidaksesuaian hasil kerja dengan brief atau keterlambatan pengerjaan..." required minlength="15"></textarea>
        <div class="form-text">Minimal 15 karakter.</div>
    </div>
</x-confirm-modal>

@push('scripts')
<script>
    // 1. Hitung Mundur Deadline
    (function () {
        const box = document.getElementById('deadlineCountdownBox');
        const timerText = document.getElementById('countdownTimerText');
        const labelText = document.getElementById('countdownLabelText');
        if (!box || !timerText) return;

        const targetDate = new Date(box.getAttribute('data-deadline')).getTime();
        if (Number.isNaN(targetDate)) return;

        function updateTimer() {
            const now = Date.now();
            const diff = targetDate - now;

            if (diff <= 0) {
                box.classList.remove('bg-light');
                box.classList.add('bg-danger', 'bg-opacity-10', 'border-danger', 'text-danger');
                if (labelText) labelText.textContent = 'Melewati Batas Waktu (Overdue)';

                const absDiff = Math.abs(diff);
                const overdueDays = Math.floor(absDiff / (1000 * 60 * 60 * 24));
                const overdueHours = Math.floor((absDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                if (overdueDays > 0) {
                    timerText.textContent = `TERLEWAT ${overdueDays} HARI ${overdueHours} JAM`;
                } else {
                    timerText.textContent = `TERLEWAT ${Math.max(1, overdueHours)} JAM`;
                }
                return;
            }

            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            const pad = (n) => String(n).padStart(2, '0');
            timerText.textContent = `${pad(days)}h : ${pad(hours)}j : ${pad(minutes)}m : ${pad(seconds)}d`;
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    })();

    // 2. Chat Workspace: Kirim AJAX & Polling Berkala
    (function () {
        const container = document.getElementById('workspaceChatContainer');
        if (!container) return;

        const messagesUrl = container.getAttribute('data-messages-url');
        let lastId = parseInt(container.getAttribute('data-last-id') || '0', 10);
        let currentStatus = container.getAttribute('data-order-status') || '';
        let currentDeliverables = parseInt(container.getAttribute('data-deliverables-count') || '0', 10);

        const form = document.getElementById('workspaceMessageForm');
        const messageInput = document.getElementById('workspaceMessageInput');
        const attachmentInput = document.getElementById('workspaceAttachmentInput');
        const attachmentLabel = document.getElementById('workspaceAttachmentLabel');
        const sendBtn = document.getElementById('workspaceSendBtn');
        const errorBox = document.getElementById('chatErrorBox');
        const syncAlert = document.getElementById('workspaceStatusSyncAlert');
        const syncText = document.getElementById('workspaceStatusSyncText');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const defaultAttachmentHelp = 'Maks. 10 MB (JPG, PNG, WEBP, PDF, ZIP, DOCX)';

        // Scroll ke pesan terbaru saat pertama kali membuka workspace
        container.scrollTop = container.scrollHeight;

        if (attachmentInput && attachmentLabel) {
            attachmentInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                attachmentLabel.textContent = file ? `Dipilih: ${file.name}` : defaultAttachmentHelp;
            });
        }

        function escapeHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderMessageElement(msg) {
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-message-id', String(msg.id));

            if (msg.is_system) {
                wrapper.className = 'text-center my-1';
                wrapper.innerHTML = `
                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-3 py-2 fw-normal text-wrap lh-base">
                        <i class="bi bi-info-circle me-1"></i>${escapeHtml(msg.message)} (${escapeHtml(msg.time)})
                    </span>
                `;
                return wrapper;
            }

            wrapper.className = `d-flex flex-column ${msg.is_own ? 'align-items-end' : 'align-items-start'}`;

            const roleBadgeClass = msg.sender_role === 'seller' ? 'text-bg-success' : 'text-bg-primary';
            const roleLabel = msg.sender_role === 'seller' ? 'Penjual' : 'Pembeli';
            const bubbleClass = msg.is_delivery
                ? 'border border-success bg-success bg-opacity-10 text-dark'
                : (msg.is_own ? 'bg-primary text-white' : 'bg-light border text-dark');

            let deliveryHeader = '';
            if (msg.is_delivery) {
                deliveryHeader = `
                    <div class="fw-bold text-success small mb-1">
                        <i class="bi bi-box-seam-fill me-1"></i>PENGIRIMAN HASIL KERJA
                    </div>
                `;
            }

            let bodyHtml = '';
            if (msg.message) {
                bodyHtml = `<div class="mb-0" style="white-space: pre-line;">${escapeHtml(msg.message)}</div>`;
            }

            let attachmentHtml = '';
            if (msg.attachment_name) {
                const borderClass = msg.is_own && !msg.is_delivery ? 'border-light border-opacity-25' : '';
                const linkClass = msg.is_own && !msg.is_delivery ? 'text-white' : 'text-primary';
                const fileNode = msg.attachment_url
                    ? `<a href="${escapeHtml(msg.attachment_url)}" class="${linkClass} text-decoration-underline">${escapeHtml(msg.attachment_name)}</a>`
                    : `<span>${escapeHtml(msg.attachment_name)}</span>`;

                attachmentHtml = `
                    <div class="mt-2 pt-2 border-top ${borderClass} small d-flex align-items-center gap-2">
                        <i class="bi bi-paperclip"></i>
                        ${fileNode}
                    </div>
                `;
            }

            wrapper.innerHTML = `
                <div class="small text-muted mb-1 px-1">
                    <strong>${escapeHtml(msg.sender_name)}</strong>
                    <span class="badge ${roleBadgeClass} ms-1" style="font-size: 0.65rem;">${roleLabel}</span>
                    <span class="ms-1">${escapeHtml(msg.time)}</span>
                </div>
                <div class="p-3 rounded-3 shadow-sm ${bubbleClass}" style="max-width: 82%;">
                    ${deliveryHeader}
                    ${bodyHtml}
                    ${attachmentHtml}
                </div>
            `;

            return wrapper;
        }

        function appendMessages(list) {
            if (!Array.isArray(list) || list.length === 0) return;

            const emptyPlaceholder = document.getElementById('emptyChatPlaceholder');
            if (emptyPlaceholder) emptyPlaceholder.remove();

            const nearBottom = (container.scrollHeight - container.scrollTop - container.clientHeight) < 120;

            list.forEach((msg) => {
                if (container.querySelector(`[data-message-id="${msg.id}"]`)) return;
                container.appendChild(renderMessageElement(msg));
                if (msg.id > lastId) lastId = msg.id;
            });

            if (nearBottom) {
                container.scrollTop = container.scrollHeight;
            }
        }

        function showChatError(text) {
            if (!errorBox) return;
            errorBox.textContent = text;
            errorBox.classList.remove('d-none');
        }

        function hideChatError() {
            if (!errorBox) return;
            errorBox.textContent = '';
            errorBox.classList.add('d-none');
        }

        // Kirim pesan via Fetch AJAX agar pengalaman chat mulus tanpa reload penuh
        if (form) {
            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                hideChatError();

                const textVal = (messageInput?.value || '').trim();
                const hasFile = attachmentInput && attachmentInput.files && attachmentInput.files.length > 0;

                if (!textVal && !hasFile) {
                    showChatError('Tulis pesan atau lampirkan file terlebih dahulu.');
                    return;
                }

                if (sendBtn) sendBtn.disabled = true;

                try {
                    const formData = new FormData(form);
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    });

                    const data = await res.json();

                    if (!res.ok) {
                        const firstError = data?.errors
                            ? Object.values(data.errors).flat()[0]
                            : (data?.message || 'Gagal mengirim pesan.');
                        showChatError(firstError);
                        return;
                    }

                    if (data?.message) {
                        appendMessages([data.message]);
                        container.scrollTop = container.scrollHeight;
                    }

                    if (messageInput) messageInput.value = '';
                    if (attachmentInput) attachmentInput.value = '';
                    if (attachmentLabel) attachmentLabel.textContent = defaultAttachmentHelp;
                } catch (err) {
                    showChatError('Terjadi kendala jaringan. Silakan coba lagi.');
                } finally {
                    if (sendBtn) sendBtn.disabled = false;
                }
            });
        }

        // Polling AJAX setiap 4 detik saat tab aktif
        let pollingInFlight = false;

        async function pollMessages() {
            if (pollingInFlight || document.visibilityState !== 'visible') return;
            pollingInFlight = true;

            try {
                const url = `${messagesUrl}?after_id=${encodeURIComponent(lastId)}`;
                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!res.ok) return;
                const data = await res.json();

                if (Array.isArray(data.messages) && data.messages.length > 0) {
                    appendMessages(data.messages);
                }

                const statusChanged = data.order_status && data.order_status !== currentStatus;
                const deliveryAdded = typeof data.deliverables_count === 'number' && data.deliverables_count !== currentDeliverables;

                if ((statusChanged || deliveryAdded) && syncAlert && syncText) {
                    syncText.textContent = statusChanged
                        ? `Status pesanan berubah menjadi "${data.order_status}". Muat ulang untuk memperbarui daftar file dan tombol aksi.`
                        : 'Ada file hasil kerja baru yang diunggah. Muat ulang untuk melihat daftar file terbaru.';
                    syncAlert.classList.remove('d-none');
                    syncAlert.classList.add('d-flex');
                    currentStatus = data.order_status || currentStatus;
                    if (typeof data.deliverables_count === 'number') {
                        currentDeliverables = data.deliverables_count;
                    }
                }
            } catch (err) {
                // Abaikan gangguan jaringan sementara, coba lagi pada siklus polling berikutnya
            } finally {
                pollingInFlight = false;
            }
        }

        setInterval(pollMessages, 4000);
    })();
</script>
@endpush
@endsection

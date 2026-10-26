@extends('layouts.app')

@section('title', 'Workspace Pesanan ' . $orderData['invoice_no'])

@section('content')
@php
    $currentRole = auth()->user()->role ?? 'buyer';
    $remainingRevisions = max(0, $orderData['revision_limit'] - $orderData['revision_used']);
    $isReadOnly = in_array($orderData['status'], ['completed', 'disputed', 'cancelled']);
@endphp

<!-- Demo Switcher Skenario Status Pesanan (Memudahkan Pengujian Tahap 1) -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center bg-white border rounded-3 p-2 px-3 mb-3 shadow-sm gap-2">
    <div class="small text-muted">
        <i class="bi bi-sliders text-primary me-1"></i>
        <strong>Mode Pratinjau Workspace:</strong> Pilih contoh pesanan untuk melihat tampilan status berbeda:
    </div>
    <div class="btn-group btn-group-sm">
        @foreach ($scenarios as $id => $sc)
            <a href="{{ route('workspace.show', $id) }}"
               class="btn {{ $orderData['id'] === $id ? 'btn-primary' : 'btn-outline-secondary' }}">
                #{{ $id }} ({{ $sc['status'] }})
            </a>
        @endforeach
    </div>
</div>

<!-- Header Workspace -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                    <span class="badge text-bg-dark">{{ $orderData['invoice_no'] }}</span>
                    <span class="badge bg-primary bg-opacity-10 text-primary">Paket {{ $orderData['tier_name'] }}</span>
                    <x-status-badge :status="$orderData['status']" />
                </div>
                <h1 class="h4 fw-bold mb-1">{{ $orderData['service_title'] }}</h1>
                <div class="small text-muted d-flex flex-wrap gap-3">
                    <span><i class="bi bi-person-check me-1"></i>Pembeli: <strong class="text-dark">{{ $orderData['buyer_name'] }}</strong></span>
                    <span><i class="bi bi-shop me-1"></i>Penjual: <strong class="text-dark">{{ $orderData['seller_name'] }}</strong></span>
                    <span><i class="bi bi-cash-coin me-1"></i>Nilai Escrow: <strong class="text-success">Rp {{ number_format($orderData['total_price'], 0, ',', '.') }}</strong></span>
                </div>
            </div>
            <div class="d-flex gap-2">
                @if ($currentRole === 'seller')
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

@if ($orderData['status'] === 'disputed')
    <x-alert type="danger" title="Pesanan Sedang Dalam Sengketa (Dispute)" :dismissible="false" class="mb-4">
        Workspace ini berada dalam mode <strong>baca-saja (read-only)</strong> sambil menunggu keputusan mediasi dari Admin di Dispute Center.
    </x-alert>
@endif

<div class="row g-4">
    <!-- Kolom Kiri (8 Kolom): Kartu Brief, Daftar File Hasil, dan Ruang Chat -->
    <div class="col-lg-8">
        <!-- 1. Kartu Brief Proyek -->
        <x-card title="Brief &amp; Spesifikasi Proyek" subtitle="Instruksi resmi dari pembeli saat pemesanan" icon="bi-journal-text" class="mb-4">
            <div class="p-3 rounded-3 bg-light border-start border-4 border-primary">
                <p class="mb-0 text-dark" style="line-height: 1.65;">{{ $orderData['project_brief'] }}</p>
            </div>
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
                                <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2 px-3">
                                    <i class="bi bi-file-earmark-zip-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-semibold text-dark">{{ $item['filename'] }}</span>
                                        <span class="badge text-bg-light border">{{ $item['version'] }}</span>
                                    </div>
                                    <div class="small text-secondary mt-1">{{ $item['note'] }}</div>
                                    <div class="text-muted" style="font-size: 0.76rem;">
                                        Ukuran: {{ $item['filesize'] }} • Diunggah pada {{ $item['uploaded_at'] }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <a href="#unduh-simulasi" onclick="alert('Simulasi Tahap 1: Mengunduh {{ $item['filename'] }}'); return false;" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>Unduh File
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($currentRole === 'seller' && !$isReadOnly)
                <div class="mt-3 pt-3 border-top">
                    <form action="{{ route('workspace.deliver', $orderData['id']) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <h6 class="fw-semibold small text-uppercase text-muted mb-2">
                            <i class="bi bi-cloud-upload me-1"></i>Unggah Hasil Kerja (Khusus Penjual)
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <input type="file" name="delivery_file" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="delivery_note" class="form-control form-control-sm" placeholder="Catatan pengiriman hasil kerja..." required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success btn-sm w-100">Kirim Hasil</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </x-card>

        <!-- 3. Ruang Chat Diskusi Pembeli & Penjual -->
        <x-card title="Diskusi Workspace" subtitle="Komunikasi terekam sebagai bukti resmi transaksi Escrow" icon="bi-chat-dots-fill">
            <div class="d-flex flex-column gap-3 mb-4 pe-1" style="max-height: 460px; overflow-y: auto;">
                @foreach ($messages as $msg)
                    @if ($msg['is_system'])
                        <div class="text-center my-1">
                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning px-3 py-2 fw-normal">
                                <i class="bi bi-info-circle me-1"></i>{{ $msg['message'] }} ({{ $msg['time'] }})
                            </span>
                        </div>
                    @else
                        @php
                            $isOwn = ($currentRole === $msg['sender_role']);
                        @endphp
                        <div class="d-flex flex-column {{ $isOwn ? 'align-items-end' : 'align-items-start' }}">
                            <div class="small text-muted mb-1 px-1">
                                <strong>{{ $msg['sender_name'] }}</strong>
                                <span class="badge {{ $msg['sender_role'] === 'seller' ? 'text-bg-success' : 'text-bg-primary' }} ms-1" style="font-size: 0.65rem;">
                                    {{ $msg['sender_role'] === 'seller' ? 'Penjual' : 'Pembeli' }}
                                </span>
                                <span class="ms-1">{{ $msg['time'] }}</span>
                            </div>

                            <div class="p-3 rounded-3 shadow-sm {{ $msg['is_delivery'] ? 'border border-success bg-success bg-opacity-10 text-dark' : ($isOwn ? 'bg-primary text-white' : 'bg-light border text-dark') }}" style="max-width: 82%;">
                                @if ($msg['is_delivery'])
                                    <div class="fw-bold text-success small mb-1">
                                        <i class="bi bi-box-seam-fill me-1"></i>PENGIRIMAN HASIL KERJA
                                    </div>
                                @endif

                                <div class="mb-1">{{ $msg['message'] }}</div>

                                @if ($msg['attachment'])
                                    <div class="mt-2 pt-2 border-top {{ $isOwn && !$msg['is_delivery'] ? 'border-light border-opacity-25' : '' }} small d-flex align-items-center gap-2">
                                        <i class="bi bi-paperclip"></i>
                                        <span class="text-decoration-underline">{{ $msg['attachment'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Form Kirim Pesan -->
            @if ($isReadOnly)
                <div class="alert alert-secondary small mb-0 text-center">
                    <i class="bi bi-lock-fill me-1"></i>Form pesan dinonaktifkan karena pesanan berstatus <strong>{{ $orderData['status'] }}</strong>.
                </div>
            @else
                <form action="{{ route('workspace.message', $orderData['id']) }}" method="POST" class="border-top pt-3">
                    @csrf
                    <div class="mb-2">
                        <textarea name="message" rows="2" class="form-control" placeholder="Tulis pesan balasan atau instruksi tambahan di sini..." required></textarea>
                    </div>
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label class="btn btn-outline-secondary btn-sm mb-0">
                                <i class="bi bi-paperclip me-1"></i>Lampirkan File
                                <input type="file" name="attachment" class="d-none">
                            </label>
                            <span class="text-muted small">Maks. 10MB (JPG, PNG, PDF, ZIP, DOCX)</span>
                        </div>
                        <x-button type="submit" variant="primary" size="sm" icon="bi-send-fill">
                            Kirim Pesan
                        </x-button>
                    </div>
                </form>
            @endif
        </x-card>
    </div>

    <!-- Kolom Kanan (4 Kolom): Hitung Mundur Deadline, Kuota Revisi, & Aksi Pesanan -->
    <div class="col-lg-4">
        <!-- 1. Hitung Mundur Deadline -->
        <x-card title="Batas Waktu Pengerjaan" icon="bi-alarm-fill" class="mb-4">
            <div id="deadlineCountdownBox"
                 data-deadline="{{ $orderData['deadline_iso'] }}"
                 class="p-3 rounded-3 text-center border {{ $orderData['is_overdue'] ? 'bg-danger bg-opacity-10 border-danger text-danger' : 'bg-light' }}">
                <div class="small text-uppercase fw-semibold mb-1">
                    {{ $orderData['is_overdue'] ? 'Melewati Batas Waktu (Overdue)' : 'Hitung Mundur Deadline' }}
                </div>
                <div class="fs-4 fw-bold font-monospace" id="countdownTimerText">
                    {{ $orderData['is_overdue'] ? 'TERLEWAT 18 JAM' : '02h : 05j : 00m : 00d' }}
                </div>
                <div class="small mt-1 text-muted">
                    Target selesai: <strong>{{ $orderData['deadline_display'] }}</strong>
                </div>
            </div>
        </x-card>

        <!-- 2. Sisa Kuota Revisi -->
        <x-card title="Kuota Revisi Paket" icon="bi-arrow-repeat" class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted small">Revisi Terpakai</span>
                <span class="fw-bold">{{ $orderData['revision_used'] }} dari {{ $orderData['revision_limit'] }}x</span>
            </div>
            @php
                $revPercent = $orderData['revision_limit'] > 0
                    ? min(100, round(($orderData['revision_used'] / $orderData['revision_limit']) * 100))
                    : 100;
            @endphp
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
        <x-card title="Keputusan &amp; Aksi Pesanan" subtitle="Tindakan pembeli dan penjual pada pesanan ini" icon="bi-sliders2-vertical">
            <div class="d-grid gap-2">
                <!-- Tombol Setujui Hasil (Membuka Confirm Modal) -->
                <button type="button"
                        class="btn btn-success fw-semibold d-flex align-items-center justify-content-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#approveOrderModal"
                        @disabled($isReadOnly)>
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Setujui Hasil &amp; Selesaikan</span>
                </button>

                <!-- Tombol Minta Revisi (Aktif bila kuota revisi masih ada) -->
                <button type="button"
                        class="btn btn-warning text-dark fw-semibold d-flex align-items-center justify-content-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#requestRevisionModal"
                        @disabled($isReadOnly || $remainingRevisions <= 0)>
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Minta Revisi ({{ $remainingRevisions }} tersisa)</span>
                </button>

                <hr class="my-2">

                <!-- Tombol Ajukan Sengketa / Dispute -->
                <button type="button"
                        class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#openDisputeModal"
                        @disabled($isReadOnly)>
                    <i class="bi bi-exclamation-octagon"></i>
                    <span>Ajukan Sengketa ke Admin</span>
                </button>
            </div>

            <div class="mt-3 p-2 rounded bg-light small text-muted" style="font-size: 0.76rem;">
                <i class="bi bi-shield-check text-success me-1"></i>
                Menekan <strong>Setujui Hasil</strong> akan mengubah status pesanan menjadi <code>completed</code> dan mencairkan dana Escrow ke saldo penjual.
            </div>
        </x-card>
    </div>
</div>

<!-- Modal 1: Konfirmasi Setujui Hasil -->
<x-confirm-modal
    id="approveOrderModal"
    title="Setujui Hasil Pekerjaan?"
    message="Dengan menyetujui hasil pekerjaan ini, pesanan akan dinyatakan Selesai (completed) dan dana di Escrow sebesar Rp {{ number_format($orderData['total_price'], 0, ',', '.') }} akan diteruskan ke saldo penjual."
    :action="route('workspace.approve', $orderData['id'])"
    method="POST"
    confirmText="Ya, Setujui &amp; Cairkan Escrow"
    variant="success"
/>

<!-- Modal 2: Minta Revisi -->
<x-confirm-modal
    id="requestRevisionModal"
    title="Ajukan Permintaan Revisi"
    message="Tuliskan bagian yang perlu diperbaiki oleh penjual. Permintaan ini akan menggunakan 1 kuota revisi Anda (tersisa {{ $remainingRevisions }} kuota)."
    :action="route('workspace.revision', $orderData['id'])"
    method="POST"
    confirmText="Kirim Permintaan Revisi"
    variant="warning"
>
    <div class="mt-2">
        <label class="form-label small fw-semibold">Catatan Detail Revisi <span class="text-danger">*</span></label>
        <textarea name="revision_note" rows="3" class="form-control" placeholder="Contoh: Tolong warna teks pada logo dibuat lebih terang dan ukuran ikon diperbesar sedikit..." required></textarea>
    </div>
</x-confirm-modal>

<!-- Modal 3: Ajukan Sengketa -->
<x-confirm-modal
    id="openDisputeModal"
    title="Ajukan Sengketa (Dispute) ke Admin"
    message="Jika terjadi kendala serius yang tidak dapat diselesaikan bersama penjual, Admin akan memediasi dan memeriksa riwayat chat serta file hasil di workspace."
    :action="route('workspace.dispute', $orderData['id'])"
    method="POST"
    confirmText="Ajukan Sengketa"
    variant="danger"
>
    <div class="mt-2">
        <label class="form-label small fw-semibold">Alasan Sengketa <span class="text-danger">*</span></label>
        <textarea name="reason" rows="3" class="form-control" placeholder="Jelaskan ketidaksesuaian hasil kerja dengan brief atau keterlambatan pengerjaan..." required></textarea>
    </div>
</x-confirm-modal>

@push('scripts')
<script>
    (function () {
        const box = document.getElementById('deadlineCountdownBox');
        const timerText = document.getElementById('countdownTimerText');
        if (!box || !timerText) return;

        const targetDate = new Date(box.getAttribute('data-deadline')).getTime();

        function updateTimer() {
            const now = new Date().getTime();
            const diff = targetDate - now;

            if (diff <= 0) {
                box.classList.remove('bg-light');
                box.classList.add('bg-danger', 'bg-opacity-10', 'border-danger', 'text-danger');
                const overdueHours = Math.max(1, Math.floor(Math.abs(diff) / (1000 * 60 * 60)));
                timerText.textContent = 'TERLEWAT ' + overdueHours + ' JAM';
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
</script>
@endpush
@endsection

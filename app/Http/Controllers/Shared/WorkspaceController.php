<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\DeliverWorkRequest;
use App\Http\Requests\Workspace\RequestRevisionRequest;
use App\Http\Requests\Workspace\SendMessageRequest;
use App\Models\Order;
use App\Models\User;
use App\Models\WorkspaceMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WorkspaceController extends Controller
{
    /**
     * Arahkan /workspace ke pesanan terbaru milik pengguna yang sudah memiliki workspace.
     */
    public function index(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role === 'seller') {
            $order = Order::query()
                ->whereHas('servicePackage.service', fn ($q) => $q->where('seller_id', $user->id))
                ->whereIn('status', Order::WORKSPACE_STATUSES)
                ->latest()
                ->first();

            if ($order) {
                return redirect()->route('workspace.show', $order);
            }

            return redirect()
                ->route('seller.orders.index')
                ->with('info', 'Belum ada pesanan aktif yang memiliki workspace.');
        }

        $order = Order::query()
            ->where('buyer_id', $user->id)
            ->whereIn('status', Order::WORKSPACE_STATUSES)
            ->latest()
            ->first();

        if ($order) {
            return redirect()->route('workspace.show', $order);
        }

        return redirect()
            ->route('buyer.orders.index')
            ->with('info', 'Belum ada pesanan aktif yang memiliki workspace.');
    }

    /**
     * Tampilkan ruang kerja pesanan dengan otorisasi ketat lewat OrderPolicy@view.
     */
    public function show(Request $request, Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load([
            'buyer:id,name,email,role',
            'servicePackage.service.category:id,name',
            'servicePackage.service.seller:id,name,email,role',
            'messages.sender:id,name,role',
            'review',
        ]);

        // Pastikan entri tabel workspaces tersedia untuk pesanan yang sudah dibayar
        if (in_array($order->status, Order::WORKSPACE_STATUSES, true)) {
            $order->workspace()->firstOrCreate([]);
        }

        $user = $request->user();
        $isBuyer = (int) $user->id === (int) $order->buyer_id;
        $isSeller = $this->isSeller($user, $order);

        $revisionLimit = $order->revisionLimit();
        $revisionUsed = (int) $order->revision_count;
        $remainingRevisions = $order->remainingRevisions();
        $isReadOnly = $order->isWorkspaceReadOnly();

        $canDeliver = $isSeller && in_array($order->status, ['in_progress', 'revision', 'delivered'], true);
        $canRequestRevision = $isBuyer && $order->status === 'delivered' && $remainingRevisions > 0;
        $canApprove = $isBuyer && $order->status === 'delivered';
        $canOpenDispute = in_array($order->status, ['in_progress', 'delivered', 'revision'], true);

        $rawMessages = $order->messages->sortBy('id')->values();
        $messages = $rawMessages
            ->map(fn (WorkspaceMessage $msg) => $this->formatMessage($msg, $order, $user->id))
            ->all();

        $deliveryMessages = $rawMessages
            ->where('is_delivery', true)
            ->values();

        $totalDeliveries = $deliveryMessages->count();
        $deliverables = $deliveryMessages
            ->reverse()
            ->values()
            ->map(function (WorkspaceMessage $msg, int $index) use ($order, $totalDeliveries) {
                $versionNumber = $totalDeliveries - $index;
                $versionLabel = $index === 0
                    ? "Versi {$versionNumber} (Terbaru)"
                    : "Versi {$versionNumber}";

                $hasFile = !empty($msg->attachment_url) && Storage::disk('public')->exists($msg->attachment_url);
                $fileSize = $hasFile
                    ? $this->formatFileSize(Storage::disk('public')->size($msg->attachment_url))
                    : null;

                return [
                    'id'          => $msg->id,
                    'version'     => $versionLabel,
                    'filename'    => $msg->attachmentName() ?? 'Lampiran hasil kerja',
                    'filesize'    => $fileSize,
                    'uploaded_at' => $msg->created_at?->format('d M Y, H:i') ?? '-',
                    'note'        => $msg->message ?? '',
                    'has_file'    => $hasFile,
                    'download_url'=> $hasFile ? route('workspace.attachment', [$order->id, $msg->id]) : null,
                ];
            })
            ->all();

        $lastMessageId = (int) ($rawMessages->last()?->id ?? 0);

        return view('shared.workspace.show', compact(
            'order',
            'messages',
            'deliverables',
            'lastMessageId',
            'isBuyer',
            'isSeller',
            'revisionLimit',
            'revisionUsed',
            'remainingRevisions',
            'isReadOnly',
            'canDeliver',
            'canRequestRevision',
            'canApprove',
            'canOpenDispute'
        ));
    }

    /**
     * Endpoint polling AJAX untuk mengambil pesan baru dan status terkini pesanan.
     */
    public function messages(Request $request, Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->loadMissing('servicePackage.service');
        $afterId = max(0, (int) $request->query('after_id', 0));
        $userId = (int) $request->user()->id;

        $newMessages = $order->messages()
            ->with('sender:id,name,role')
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->get()
            ->map(fn (WorkspaceMessage $msg) => $this->formatMessage($msg, $order, $userId))
            ->values()
            ->all();

        return response()->json([
            'messages'            => $newMessages,
            'order_status'        => $order->status,
            'revision_count'      => (int) $order->revision_count,
            'revision_limit'      => $order->revisionLimit(),
            'remaining_revisions' => $order->remainingRevisions(),
            'is_read_only'        => $order->isWorkspaceReadOnly(),
            'deliverables_count'  => $order->messages()->where('is_delivery', true)->count(),
        ]);
    }

    /**
     * Kirim pesan diskusi (dan lampiran opsional) ke ruang kerja.
     */
    public function sendMessage(SendMessageRequest $request, Order $order): RedirectResponse|JsonResponse
    {
        Gate::authorize('view', $order);

        if ($order->isWorkspaceReadOnly()) {
            $errorMsg = 'Workspace sedang dalam mode baca-saja sehingga pesan baru tidak dapat dikirim.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $errorMsg], 422);
            }

            return back()->with('error', $errorMsg);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $this->storeWorkspaceFile(
                $request->file('attachment'),
                "workspace/order-{$order->id}/attachments"
            );
        }

        $message = WorkspaceMessage::create([
            'order_id'       => $order->id,
            'sender_id'      => $request->user()->id,
            'message'        => trim((string) ($request->validated('message') ?? '')),
            'attachment_url' => $attachmentPath,
            'is_delivery'    => false,
        ]);

        $message->load('sender:id,name,role');

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => 'ok',
                'message' => $this->formatMessage($message, $order, (int) $request->user()->id),
            ], 201);
        }

        return back()->with('status', 'Pesan berhasil dikirim ke ruang kerja.');
    }

    /**
     * Penjual mengunggah hasil pekerjaan resmi (is_delivery = true) dan mengubah status ke delivered.
     */
    public function deliverWork(DeliverWorkRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        abort_unless(
            $this->isSeller($request->user(), $order),
            403,
            'Hanya penjual dari pesanan ini yang dapat mengunggah hasil kerja.'
        );

        $file = $request->file('delivery_file');
        $note = trim((string) $request->validated('delivery_note'));

        $result = DB::transaction(function () use ($order, $request, $file, $note) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === 'paid') {
                return ['ok' => false, 'type' => 'warning', 'message' => 'Klik tombol "Mulai Kerjakan" terlebih dahulu sebelum mengunggah hasil kerja.'];
            }

            if (!in_array($lockedOrder->status, ['in_progress', 'revision', 'delivered'], true)) {
                return ['ok' => false, 'type' => 'error', 'message' => 'Hasil kerja tidak dapat diunggah pada status pesanan saat ini.'];
            }

            $storedPath = $this->storeWorkspaceFile($file, "workspace/order-{$lockedOrder->id}/deliveries");

            WorkspaceMessage::create([
                'order_id'       => $lockedOrder->id,
                'sender_id'      => $request->user()->id,
                'message'        => $note,
                'attachment_url' => $storedPath,
                'is_delivery'    => true,
            ]);

            $lockedOrder->status = 'delivered';
            $lockedOrder->save();

            return ['ok' => true];
        });

        if (!$result['ok']) {
            return back()->with($result['type'], $result['message']);
        }

        return back()->with('status', 'File hasil kerja berhasil diunggah dan status pesanan diubah menjadi Hasil Dikirim (delivered).');
    }

    /**
     * Pembeli mengajukan permintaan revisi selama revision_count < revision_limit.
     */
    public function requestRevision(RequestRevisionRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        abort_unless(
            (int) $request->user()->id === (int) $order->buyer_id,
            403,
            'Hanya pembeli yang dapat mengajukan permintaan revisi.'
        );

        $note = trim((string) $request->validated('revision_note'));

        $result = DB::transaction(function () use ($order, $request, $note) {
            $lockedOrder = Order::with('servicePackage')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status !== 'delivered') {
                return ['ok' => false, 'message' => 'Permintaan revisi hanya dapat diajukan saat status pesanan adalah Hasil Dikirim (delivered).'];
            }

            $limit = $lockedOrder->revisionLimit();
            if ((int) $lockedOrder->revision_count >= $limit) {
                return ['ok' => false, 'message' => 'Kuota revisi untuk paket pesanan ini sudah habis.'];
            }

            $lockedOrder->revision_count = (int) $lockedOrder->revision_count + 1;
            $lockedOrder->status = 'revision';
            $lockedOrder->save();

            $remaining = max(0, $limit - $lockedOrder->revision_count);

            WorkspaceMessage::create([
                'order_id'       => $lockedOrder->id,
                'sender_id'      => $request->user()->id,
                'message'        => WorkspaceMessage::SYSTEM_PREFIX
                    . "Pembeli mengajukan Permintaan Revisi #{$lockedOrder->revision_count} "
                    . "(Sisa kuota revisi: {$remaining} dari {$limit}). Catatan: {$note}",
                'attachment_url' => null,
                'is_delivery'    => false,
            ]);

            return [
                'ok'        => true,
                'used'      => $lockedOrder->revision_count,
                'remaining' => $remaining,
            ];
        });

        if (!$result['ok']) {
            return back()->with('error', $result['message']);
        }

        return back()->with(
            'warning',
            "Permintaan revisi #{$result['used']} berhasil dikirim ke penjual. Sisa kuota revisi: {$result['remaining']}."
        );
    }

    /**
     * Pembeli menyetujui hasil pekerjaan -> status menjadi completed.
     * Pemanggilan EscrowService::release() akan dihubungkan pada Tahap 3.
     */
    public function approveOrder(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        abort_unless(
            (int) $request->user()->id === (int) $order->buyer_id,
            403,
            'Hanya pembeli yang dapat menyetujui hasil pekerjaan.'
        );

        $result = DB::transaction(function () use ($order, $request) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === 'completed') {
                return ['ok' => true, 'already' => true];
            }

            if ($lockedOrder->status !== 'delivered') {
                return ['ok' => false, 'message' => 'Pesanan hanya dapat disetujui setelah penjual mengunggah hasil kerja (status delivered).'];
            }

            $lockedOrder->status = 'completed';
            $lockedOrder->save();

            // TODO (Tahap 3): Panggil EscrowService::release($lockedOrder) saat EscrowService milik Faqih sudah tersedia.

            WorkspaceMessage::create([
                'order_id'       => $lockedOrder->id,
                'sender_id'      => $request->user()->id,
                'message'        => WorkspaceMessage::SYSTEM_PREFIX
                    . 'Pembeli menyetujui hasil pekerjaan. Pesanan dinyatakan Selesai (completed).',
                'attachment_url' => null,
                'is_delivery'    => false,
            ]);

            return ['ok' => true, 'already' => false];
        });

        if (!$result['ok']) {
            return back()->with('error', $result['message']);
        }

        return redirect()
            ->route('buyer.reviews.create', $order->id)
            ->with('status', 'Hasil pekerjaan telah disetujui! Status pesanan menjadi Selesai (completed). Silakan berikan ulasan Anda.');
    }

    /**
     * Unduh lampiran pesan/hasil kerja dengan pemeriksaan otorisasi OrderPolicy@view.
     */
    public function downloadAttachment(Order $order, WorkspaceMessage $message): StreamedResponse
    {
        Gate::authorize('view', $order);

        abort_unless(
            (int) $message->order_id === (int) $order->id && !empty($message->attachment_url),
            404,
            'Lampiran tidak ditemukan pada pesanan ini.'
        );

        abort_unless(
            Storage::disk('public')->exists($message->attachment_url),
            404,
            'Berkas lampiran tidak ditemukan di penyimpanan.'
        );

        $downloadName = $message->attachmentName() ?? basename($message->attachment_url);

        return Storage::disk('public')->download($message->attachment_url, $downloadName);
    }

    /**
     * Pengajuan sengketa (implementasi penuh Dispute Center & dana berada di Tahap 3 bagian Annis).
     */
    public function openDispute(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        $request->validate([
            'reason' => ['required', 'string', 'min:15', 'max:1000'],
        ], [
            'reason.required' => 'Alasan pengajuan sengketa wajib diisi.',
            'reason.min'      => 'Alasan sengketa minimal 15 karakter.',
        ]);

        if (!in_array($order->status, ['in_progress', 'delivered', 'revision'], true)) {
            return back()->with('error', 'Sengketa hanya dapat diajukan untuk pesanan yang sedang berjalan.');
        }

        return back()->with('info', 'Formulir pengajuan sengketa sudah tervalidasi. Proses pencatatan & mediasi Dispute Center akan diaktifkan pada Tahap 3.');
    }

    private function isSeller(User $user, Order $order): bool
    {
        $order->loadMissing('servicePackage.service');

        return (int) $user->id === (int) ($order->servicePackage?->service?->seller_id ?? 0);
    }

    private function storeWorkspaceFile(UploadedFile $file, string $directory): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $baseName = $baseName !== '' ? Str::limit($baseName, 60, '') : 'berkas';
        $filename = time() . '_' . $baseName . ($extension !== '' ? '.' . $extension : '');

        return Storage::disk('public')->putFileAs($directory, $file, $filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMessage(WorkspaceMessage $msg, Order $order, int $currentUserId): array
    {
        $isSystem = $msg->isSystemMessage();
        $hasAttachment = !empty($msg->attachment_url) && Storage::disk('public')->exists($msg->attachment_url);

        return [
            'id'              => $msg->id,
            'sender_id'       => $msg->sender_id,
            'sender_name'     => $isSystem ? 'Sistem Escrow' : ($msg->sender->name ?? 'Pengguna'),
            'sender_role'     => $isSystem ? 'system' : ($msg->sender->role ?? 'buyer'),
            'is_own'          => !$isSystem && (int) $msg->sender_id === $currentUserId,
            'is_system'       => $isSystem,
            'is_delivery'     => (bool) $msg->is_delivery,
            'message'         => $msg->displayMessage(),
            'attachment_name' => $msg->attachmentName(),
            'attachment_url'  => $hasAttachment ? route('workspace.attachment', [$order->id, $msg->id]) : null,
            'has_attachment'  => $hasAttachment,
            'time'            => $msg->created_at?->format('d M Y, H:i') ?? '-',
        ];
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}

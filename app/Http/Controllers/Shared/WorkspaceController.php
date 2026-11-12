<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WorkspaceController extends Controller
{
    public function show(int $order = 1)
    {
        // Skenario dummy Workspace Pesanan untuk Tahap 1 (bisa dilihat oleh Pembeli maupun Penjual)
        $scenarios = [
            1 => [
                'id'               => 1,
                'invoice_no'       => 'INV-20261022-0001',
                'service_title'    => 'Desain Logo Profesional & Brand Identity',
                'category'         => 'Desain',
                'tier_name'        => 'Standard',
                'status'           => 'delivered',
                'buyer_name'       => 'Budi Pembeli',
                'seller_name'      => 'Sinta Penjual',
                'total_price'      => 300000,
                'platform_fee'     => 30000,
                'ordered_at'       => '22 Okt 2026, 14:20',
                'deadline_iso'     => now()->addDays(2)->addHours(5)->toIso8601String(),
                'deadline_display' => now()->addDays(2)->addHours(5)->format('d M Y, H:i'),
                'is_overdue'       => false,
                'revision_used'    => 1,
                'revision_limit'   => 3,
                'project_brief'    => 'Tolong buatkan logo untuk kedai kopi "Kopi Senja". Nuansa warna hangat (cokelat kopi & krem keemasan), gaya minimalis modern, cocok dipasang di cup plastik, neon box, dan foto profil Instagram. Sertakan ikon biji kopi atau cangkir dengan siluet matahari senja.',
            ],
            2 => [
                'id'               => 2,
                'invoice_no'       => 'INV-20261023-0002',
                'service_title'    => 'Pembuatan Landing Page Laravel + Bootstrap 5',
                'category'         => 'Pemrograman',
                'tier_name'        => 'Basic',
                'status'           => 'in_progress',
                'buyer_name'       => 'Budi Pembeli',
                'seller_name'      => 'Raka Web Studio',
                'total_price'      => 150000,
                'platform_fee'     => 15000,
                'ordered_at'       => '23 Okt 2026, 09:15',
                'deadline_iso'     => now()->addDays(4)->toIso8601String(),
                'deadline_display' => now()->addDays(4)->format('d M Y, H:i'),
                'is_overdue'       => false,
                'revision_used'    => 0,
                'revision_limit'   => 2,
                'project_brief'    => 'Landing page satu halaman untuk mempromosikan aplikasi kasir UMKM. Bagian yang dibutuhkan: Hero banner, Fitur Utama, Tabel Harga 3 paket, Testimoni, dan tombol CTA ke WhatsApp.',
            ],
            3 => [
                'id'               => 3,
                'invoice_no'       => 'INV-20261012-0041',
                'service_title'    => 'Edit Video Reels Instagram & TikTok 30 Detik',
                'category'         => 'Video',
                'tier_name'        => 'Standard',
                'status'           => 'disputed',
                'buyer_name'       => 'Budi Pembeli',
                'seller_name'      => 'Dimas Motion',
                'total_price'      => 275000,
                'platform_fee'     => 27500,
                'ordered_at'       => '12 Okt 2026, 19:00',
                'deadline_iso'     => now()->subHours(18)->toIso8601String(),
                'deadline_display' => now()->subHours(18)->format('d M Y, H:i'),
                'is_overdue'       => true,
                'revision_used'    => 2,
                'revision_limit'   => 2,
                'project_brief'    => 'Video promosi produk skincare 30 detik dengan subtitle bahasa Indonesia, transisi dinamis, dan musik latar bebas hak cipta.',
            ],
        ];

        $orderData = $scenarios[$order] ?? $scenarios[1];

        $messages = [
            [
                'id'             => 1,
                'sender_role'    => 'buyer',
                'sender_name'    => $orderData['buyer_name'],
                'time'           => '22 Okt 2026, 14:25',
                'message'        => 'Halo Kak, saya sudah melakukan pembayaran ke Escrow dan mengisi brief untuk logo Kopi Senja. Referensi palet warna saya lampirkan ya.',
                'attachment'     => 'referensi-palet-kopi-senja.pdf',
                'is_delivery'    => false,
                'is_system'      => false,
            ],
            [
                'id'             => 2,
                'sender_role'    => 'seller',
                'sender_name'    => $orderData['seller_name'],
                'time'           => '22 Okt 2026, 15:02',
                'message'        => 'Halo Pak Budi! Terima kasih pesanannya. Brief sudah sangat jelas, saya mulai kerjakan 3 alternatif sketsa awal hari ini ya.',
                'attachment'     => null,
                'is_delivery'    => false,
                'is_system'      => false,
            ],
            [
                'id'             => 3,
                'sender_role'    => 'seller',
                'sender_name'    => $orderData['seller_name'],
                'time'           => '24 Okt 2026, 10:30',
                'message'        => 'Berikut saya unggah hasil desain Logo Kopi Senja Versi 1 lengkap dengan preview di cup kopi dan neon box. Silakan dicek terlebih dahulu.',
                'attachment'     => 'paket-logo-kopi-senja-v1.zip',
                'is_delivery'    => true,
                'is_system'      => false,
            ],
            [
                'id'             => 4,
                'sender_role'    => 'system',
                'sender_name'    => 'Sistem Escrow',
                'time'           => '24 Okt 2026, 11:15',
                'message'        => 'Pembeli mengajukan Permintaan Revisi #1 (Sisa kuota revisi: 2 dari 3).',
                'attachment'     => null,
                'is_delivery'    => false,
                'is_system'      => true,
            ],
            [
                'id'             => 5,
                'sender_role'    => 'seller',
                'sender_name'    => $orderData['seller_name'],
                'time'           => '25 Okt 2026, 16:40',
                'message'        => 'Revisi #1 sudah selesai! Font sudah dipertebal dan warna krem dibuat lebih kontras sesuai masukan Bapak. File final v2 sudah saya lampirkan.',
                'attachment'     => 'paket-logo-kopi-senja-v2-final.zip',
                'is_delivery'    => true,
                'is_system'      => false,
            ],
        ];

        $deliverables = [
            [
                'version'     => 'Versi 2 (Terbaru)',
                'filename'    => 'paket-logo-kopi-senja-v2-final.zip',
                'filesize'    => '14.8 MB',
                'uploaded_at' => '25 Okt 2026, 16:40',
                'note'        => 'File master AI, EPS, SVG, PNG transparan + Mockup Cup & Neon Box (Revisi #1).',
            ],
            [
                'version'     => 'Versi 1',
                'filename'    => 'paket-logo-kopi-senja-v1.zip',
                'filesize'    => '12.2 MB',
                'uploaded_at' => '24 Okt 2026, 10:30',
                'note'        => 'Konsep awal 3 opsi logo + preview mockup.',
            ],
        ];

        return view('shared.workspace.show', compact(
            'orderData',
            'scenarios',
            'messages',
            'deliverables'
        ));
    }

    public function sendMessage(Request $request, int $order = 1)
    {
        $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        return back()->with('status', 'Pesan berhasil dikirim ke ruang kerja (simulasi Tahap 1).');
    }

    public function deliverWork(Request $request, int $order = 1)
    {
        return back()->with('status', 'File hasil kerja berhasil diunggah dan status pesanan diubah menjadi Hasil Dikirim (delivered).');
    }

    public function requestRevision(Request $request, int $order = 1)
    {
        $request->validate([
            'revision_note' => ['required', 'string', 'min:10'],
        ], [
            'revision_note.required' => 'Catatan revisi wajib diisi.',
            'revision_note.min'      => 'Catatan revisi minimal 10 karakter.',
        ]);

        return back()->with('warning', 'Permintaan revisi berhasil dikirim ke penjual. Kuota revisi berkurang 1 (simulasi Tahap 1).');
    }

    public function approveOrder(Request $request, int $order = 1)
    {
        return redirect()
            ->route('buyer.reviews.create', $order)
            ->with('status', 'Hasil pekerjaan telah disetujui! Status pesanan menjadi Selesai (completed). Silakan berikan ulasan Anda.');
    }

    public function openDispute(Request $request, int $order = 1)
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:15'],
        ], [
            'reason.required' => 'Alasan pengajuan sengketa wajib diisi.',
            'reason.min'      => 'Alasan sengketa minimal 15 karakter.',
        ]);

        return back()->with('error', 'Sengketa (Dispute) telah diajukan ke Admin. Workspace dikunci menjadi mode baca-saja selama proses mediasi.');
    }
}

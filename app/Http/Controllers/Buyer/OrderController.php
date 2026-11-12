<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $selectedStatus = $request->query('status', 'all');

        // Data dummy daftar pesanan pembeli untuk Tahap 1
        $allOrders = collect([
            [
                'id'            => 1,
                'invoice_no'    => 'INV-20261022-0001',
                'service_title' => 'Desain Logo Profesional',
                'category'      => 'Desain',
                'seller_name'   => 'Sinta Penjual',
                'tier_name'     => 'Basic',
                'total_price'   => 165000,
                'status'        => 'in_progress',
                'ordered_at'    => '22 Okt 2026',
                'deadline_at'   => now()->addDays(3)->format('d M Y, H:i'),
                'days_left'     => '3 hari lagi',
                'is_overdue'    => false,
                'has_review'    => false,
            ],
            [
                'id'            => 2,
                'invoice_no'    => 'INV-20261023-0002',
                'service_title' => 'Pembuatan Landing Page Laravel + Bootstrap 5',
                'category'      => 'Pemrograman',
                'seller_name'   => 'Raka Web Studio',
                'tier_name'     => 'Standard',
                'total_price'   => 330000,
                'status'        => 'delivered',
                'ordered_at'    => '23 Okt 2026',
                'deadline_at'   => now()->addDays(1)->format('d M Y, H:i'),
                'days_left'     => '1 hari lagi',
                'is_overdue'    => false,
                'has_review'    => false,
            ],
            [
                'id'            => 3,
                'invoice_no'    => 'INV-20261024-0003',
                'service_title' => 'Penulisan 10 Artikel Blog SEO Bahasa Indonesia',
                'category'      => 'Penulisan',
                'seller_name'   => 'Ayu Content Lab',
                'tier_name'     => 'Basic',
                'total_price'   => 110000,
                'status'        => 'pending_payment',
                'ordered_at'    => '24 Okt 2026',
                'deadline_at'   => '-',
                'days_left'     => 'Menunggu pembayaran',
                'is_overdue'    => false,
                'has_review'    => false,
            ],
            [
                'id'            => 4,
                'invoice_no'    => 'INV-20261015-0088',
                'service_title' => 'Penerjemahan Dokumen Abstrak Jurnal (ID-EN)',
                'category'      => 'Penerjemahan',
                'seller_name'   => 'Nadia Translator',
                'tier_name'     => 'Basic',
                'total_price'   => 132000,
                'status'        => 'completed',
                'ordered_at'    => '15 Okt 2026',
                'deadline_at'   => '18 Okt 2026, 17:00',
                'days_left'     => 'Selesai tepat waktu',
                'is_overdue'    => false,
                'has_review'    => false,
            ],
            [
                'id'            => 5,
                'invoice_no'    => 'INV-20261012-0041',
                'service_title' => 'Edit Video Reels Instagram & TikTok 30 Detik',
                'category'      => 'Video',
                'seller_name'   => 'Dimas Motion',
                'tier_name'     => 'Standard',
                'total_price'   => 275000,
                'status'        => 'disputed',
                'ordered_at'    => '12 Okt 2026',
                'deadline_at'   => '16 Okt 2026, 23:59',
                'days_left'     => 'Sedang ditinjau Admin',
                'is_overdue'    => true,
                'has_review'    => false,
            ],
        ]);

        $orders = $selectedStatus === 'all'
            ? $allOrders
            : $allOrders->where('status', $selectedStatus)->values();

        return view('buyer.orders.index', compact('orders', 'allOrders', 'selectedStatus'));
    }
}

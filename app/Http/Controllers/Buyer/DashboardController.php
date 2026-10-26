<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        // Data dummy Tahap 1 untuk Dashboard Pembeli (sesuai Blueprint Fiki Tahap 1)
        $stats = [
            'active_orders'     => 2,
            'waiting_approval'  => 1,
            'completed_orders'  => 4,
            'escrow_protected'  => 450000,
        ];

        $activeOrders = [
            [
                'id'           => 1,
                'invoice_no'   => 'INV-20261022-0001',
                'service'      => 'Desain Logo Profesional & Brand Identity',
                'seller_name'  => 'Sinta Penjual',
                'tier_name'    => 'Basic',
                'total_price'  => 150000,
                'status'       => 'in_progress',
                'deadline'     => now()->addDays(2)->format('d M Y, H:i'),
                'progress_note'=> 'Penjual sedang mengerjakan sketsa konsep logo.',
            ],
            [
                'id'           => 2,
                'invoice_no'   => 'INV-20261023-0002',
                'service'      => 'Pembuatan Landing Page Laravel + Bootstrap 5',
                'seller_name'  => 'Raka Web Studio',
                'tier_name'    => 'Standard',
                'total_price'  => 300000,
                'status'       => 'delivered',
                'deadline'     => now()->addDays(1)->format('d M Y, H:i'),
                'progress_note'=> 'File hasil kerja v1 telah diunggah. Menunggu review Anda.',
            ],
        ];

        $recentHistory = [
            [
                'id'           => 3,
                'invoice_no'   => 'INV-20261015-0088',
                'service'      => 'Penerjemahan Dokumen Abstrak Jurnal (ID-EN)',
                'seller_name'  => 'Nadia Translator',
                'tier_name'    => 'Basic',
                'total_price'  => 120000,
                'status'       => 'completed',
                'completed_at' => '18 Okt 2026',
                'has_review'   => false,
                'rating'       => null,
            ],
            [
                'id'           => 4,
                'invoice_no'   => 'INV-20261010-0054',
                'service'      => 'Video Explainer Promosi Produk UMKM 60 Detik',
                'seller_name'  => 'Dimas Motion',
                'tier_name'    => 'Premium',
                'total_price'  => 550000,
                'status'       => 'completed',
                'completed_at' => '12 Okt 2026',
                'has_review'   => true,
                'rating'       => 5,
            ],
        ];

        $savedServices = [
            [
                'id'            => 1,
                'title'         => 'Desain Logo Profesional & Pedoman Brand UMKM',
                'category'      => 'Desain',
                'seller'        => 'Sinta Penjual',
                'rating'        => 4.9,
                'reviews_count' => 28,
                'starting_price'=> 150000,
                'delivery_days' => 3,
            ],
            [
                'id'            => 2,
                'title'         => 'Optimasi & Pembuatan Website Company Profile Laravel',
                'category'      => 'Pemrograman',
                'seller'        => 'Raka Web Studio',
                'rating'        => 4.8,
                'reviews_count' => 19,
                'starting_price'=> 300000,
                'delivery_days' => 5,
            ],
            [
                'id'            => 3,
                'title'         => 'Penulisan Artikel SEO & Copywriting Landing Page',
                'category'      => 'Penulisan',
                'seller'        => 'Ayu Content Lab',
                'rating'        => 4.7,
                'reviews_count' => 14,
                'starting_price'=> 100000,
                'delivery_days' => 2,
            ],
        ];

        return view('buyer.dashboard', compact(
            'stats',
            'activeOrders',
            'recentHistory',
            'savedServices'
        ));
    }
}

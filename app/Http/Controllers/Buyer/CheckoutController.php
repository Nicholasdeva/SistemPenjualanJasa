<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(Request $request, int $package = 1)
    {
        $packages = [
            1 => [
                'id'             => 1,
                'tier_name'      => 'Basic',
                'price'          => 150000,
                'delivery_days'  => 3,
                'revision_limit' => 1,
                'features'       => [
                    '2 konsep awal desain logo',
                    'File resolusi tinggi (PNG & JPG transparan)',
                    'Maksimal 1x revisi minor',
                    'Pengerjaan 3 hari kerja',
                ],
            ],
            2 => [
                'id'             => 2,
                'tier_name'      => 'Standard',
                'price'          => 300000,
                'delivery_days'  => 5,
                'revision_limit' => 3,
                'features'       => [
                    '3 konsep awal desain logo',
                    'File master vektor (AI, EPS, SVG, PDF)',
                    'Mockup presentasi 3D realistis',
                    'Maksimal 3x revisi',
                    'Pengerjaan 5 hari kerja',
                ],
            ],
            3 => [
                'id'             => 3,
                'tier_name'      => 'Premium',
                'price'          => 500000,
                'delivery_days'  => 7,
                'revision_limit' => 5,
                'features'       => [
                    '5 konsep eksklusif desain logo',
                    'Semua file sumber + Brand Guidelines PDF lengkap',
                    'Desain kartu nama & kop surat',
                    'Maksimal 5x revisi prioritas',
                    'Pengerjaan 7 hari kerja',
                ],
            ],
        ];

        $selectedPackage = $packages[$package] ?? $packages[1];
        $platformFee = (int) round($selectedPackage['price'] * 0.10);
        $totalPrice = $selectedPackage['price'] + $platformFee;

        $service = [
            'id'          => 1,
            'title'       => 'Desain Logo Profesional & Brand Identity UMKM',
            'category'    => 'Desain',
            'seller_name' => 'Sinta Penjual',
            'is_verified' => true,
            'rating'      => 4.9,
            'reviews'     => 28,
        ];

        return view('buyer.checkout', compact(
            'service',
            'packages',
            'selectedPackage',
            'platformFee',
            'totalPrice'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_package_id' => ['required', 'integer'],
            'project_brief'      => ['required', 'string', 'min:20'],
        ], [
            'project_brief.required' => 'Brief proyek wajib diisi agar penjual memahami kebutuhan Anda.',
            'project_brief.min'      => 'Brief proyek minimal 20 karakter agar instruksi jelas.',
        ]);

        return redirect()
            ->route('workspace.show', 1)
            ->with('status', 'Pesanan dummy berhasil dibuat! Pada Tahap 2 tombol ini akan membuka popup pembayaran Midtrans Snap Sandbox.');
    }
}

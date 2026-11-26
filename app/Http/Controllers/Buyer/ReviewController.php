<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function create(int $order = 1)
    {
        $orderSummary = [
            'id'            => $order,
            'invoice_no'    => 'INV-20261022-0001',
            'service_title' => 'Desain Logo Profesional & Brand Identity',
            'category'      => 'Desain',
            'tier_name'     => 'Standard',
            'seller_name'   => 'Sinta Penjual',
            'total_price'   => 300000,
            'completed_at'  => now()->format('d M Y, H:i'),
        ];

        return view('buyer.reviews.create', compact('orderSummary'));
    }

    public function store(Request $request, int $order = 1)
    {
        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'rating.required'  => 'Silakan pilih bintang rating antara 1 sampai 5.',
            'comment.required' => 'Komentar ulasan wajib diisi.',
            'comment.min'      => 'Komentar ulasan minimal 10 karakter.',
        ]);

        return redirect()
            ->route('buyer.orders.index')
            ->with('status', "Terima kasih! Ulasan {$data['rating']} bintang berhasil dikirim (simulasi Tahap 1).");
    }
}

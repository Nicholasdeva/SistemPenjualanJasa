<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderListController extends Controller
{
    /** Halaman "Checkout & Bayar": pesanan yang menunggu pembayaran. */
    public function pendingPayments()
    {
        $orders = Order::with('servicePackage.service.seller')
            ->where('buyer_id', auth()->id())
            ->where('status', 'pending_payment')
            ->latest()
            ->get();

        return view('buyer.payments.index', compact('orders'));
    }

    /** Halaman "Form Ulasan Jasa": pesanan selesai beserta status ulasannya. */
    public function reviewable()
    {
        $orders = Order::with(['servicePackage.service.seller', 'review'])
            ->where('buyer_id', auth()->id())
            ->where('status', 'completed')
            ->latest('updated_at')
            ->get();

        return view('buyer.reviews.index', compact('orders'));
    }
}
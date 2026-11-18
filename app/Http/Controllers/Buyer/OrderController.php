<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        // 1. Data statistik ringkasan
        $stats = [
            'active_orders'     => Order::where('buyer_id', auth()->id())->whereIn('status', ['in_progress', 'delivered'])->count(),
            'waiting_approval'  => Order::where('buyer_id', auth()->id())->where('status', 'delivered')->count(),
            'completed_orders'  => Order::where('buyer_id', auth()->id())->where('status', 'completed')->count(),
            'escrow_protected'  => Order::where('buyer_id', auth()->id())->whereIn('status', ['in_progress', 'delivered'])->sum('total_price'),
        ];

        // 2. Daftar pesanan utama
        $orders = Order::where('buyer_id', auth()->id())
            ->with('servicePackage.service.seller')
            ->latest()
            ->paginate(10);

        // 3. Riwayat pesanan selesai
        $recentHistory = Order::where('buyer_id', auth()->id())
            ->where('status', 'completed')
            ->with('servicePackage.service.seller')
            ->latest()
            ->take(5)
            ->get();

        // 4. Data dummy/saved services untuk bagian sidebar/wishlist di view index
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
        ];

        return view('buyer.orders.index', compact('orders', 'stats', 'recentHistory', 'savedServices'));
    }
}
<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private const FILTERS = [
        'all'             => 'Semua',
        'pending_payment' => 'Menunggu Bayar',
        'active'          => 'Sedang Berjalan',
        'completed'       => 'Selesai',
        'other'           => 'Sengketa / Batal',
    ];

    public function index(Request $request)
    {
        $buyerId = $request->user()->id;
        $requested = (string) $request->query('status', 'all');
        $filter = array_key_exists($requested, self::FILTERS) ? $requested : 'all';

        $base = Order::query()->where('buyer_id', $buyerId);

        $counts = [
            'all'             => (clone $base)->count(),
            'pending_payment' => (clone $base)->whereIn('status', ['pending_payment', 'pending'])->count(),
            'active'          => (clone $base)->whereIn('status', ['paid', 'in_progress', 'delivered', 'revision'])->count(),
            'completed'       => (clone $base)->where('status', 'completed')->count(),
            'other'           => (clone $base)->whereIn('status', ['disputed', 'cancelled'])->count(),
        ];

        $orders = (clone $base)
            ->with(['servicePackage.service.seller:id,name', 'review'])
            ->when($filter === 'pending_payment', fn ($q) => $q->whereIn('status', ['pending_payment', 'pending']))
            ->when($filter === 'active', fn ($q) => $q->whereIn('status', ['paid', 'in_progress', 'delivered', 'revision']))
            ->when($filter === 'completed', fn ($q) => $q->where('status', 'completed'))
            ->when($filter === 'other', fn ($q) => $q->whereIn('status', ['disputed', 'cancelled']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('buyer.orders.index', [
            'orders'  => $orders,
            'filter'  => $filter,
            'filters' => self::FILTERS,
            'counts'  => $counts,
        ]);
    }
}
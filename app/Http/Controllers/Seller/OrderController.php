<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    /** Status yang bisa dipakai sebagai filter tab */
    private const FILTERS = [
        'all'        => 'Semua',
        'paid'       => 'Perlu dikerjakan',
        'active'     => 'Sedang berjalan',
        'completed'  => 'Selesai',
    ];

    public function index(Request $request)
    {
        $filter = array_key_exists($request->query('status'), self::FILTERS) ? $request->query('status') : 'all';

        $base = Order::query()
            ->whereHas('servicePackage.service', fn ($q) => $q->where('seller_id', $request->user()->id));

        $counts = [
            'all'       => (clone $base)->count(),
            'paid'      => (clone $base)->where('status', 'paid')->count(),
            'active'    => (clone $base)->whereIn('status', ['in_progress', 'delivered', 'revision'])->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
        ];

        $orders = (clone $base)
            ->with(['servicePackage.service', 'buyer:id,name'])
            ->when($filter === 'paid', fn ($q) => $q->where('status', 'paid'))
            ->when($filter === 'active', fn ($q) => $q->whereIn('status', ['in_progress', 'delivered', 'revision']))
            ->when($filter === 'completed', fn ($q) => $q->where('status', 'completed'))
            ->latest()
            ->get();

        return view('seller.orders.index', [
            'orders'  => $orders,
            'filter'  => $filter,
            'filters' => self::FILTERS,
            'counts'  => $counts,
        ]);
    }

    /**
     * Penjual menerima & mulai mengerjakan pesanan: paid -> in_progress.
     */
    public function start(Order $order)
    {
        // Dikunci agar klik ganda tidak memproses dua kali
        $started = DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            Gate::authorize('start', $order);

            $order->status = 'in_progress';
            $order->save();

            return $order;
        });

        return redirect()->route('seller.orders.index', ['status' => 'active'])
            ->with('status', "Pesanan {$started->invoice_no} mulai dikerjakan. Pembeli bisa memantau progresnya di workspace.");
    }
}

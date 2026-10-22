<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('servicePackage.service')
            ->whereHas('servicePackage.service', fn ($q) => $q->where('seller_id', auth()->id()))
            ->latest()
            ->get();

        return view('seller.orders.index', compact('orders'));
    }
}
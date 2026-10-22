<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;

class DashboardController extends Controller
{
    public function index()
    {
        $sellerId = auth()->id();
        $profile  = auth()->user()->sellerProfile;

        $activeOrders = Order::whereIn('status', ['paid', 'in_progress', 'revision'])
            ->whereHas('servicePackage.service', fn ($q) => $q->where('seller_id', $sellerId))
            ->count();

        $averageRating = Review::whereHas('service', fn ($q) => $q->where('seller_id', $sellerId))
            ->avg('rating');

        return view('seller.dashboard', [
            'activeOrders'  => $activeOrders,
            'walletBalance' => $profile->wallet_balance ?? 0,
            'escrowBalance' => $profile->escrow_balance ?? 0,
            'averageRating' => $averageRating ? round($averageRating, 1) : 0,
        ]);
    }
}
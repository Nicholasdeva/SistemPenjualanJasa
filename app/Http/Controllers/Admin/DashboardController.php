<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use App\Models\Withdrawal;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'users'               => User::count(),
            'sellers_pending'     => SellerProfile::where('is_verified', false)->count(),
            'services'            => Service::count(),
            'orders'              => Order::count(),
            'escrow_total'        => SellerProfile::sum('escrow_balance'),
            'withdrawals_pending' => Withdrawal::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
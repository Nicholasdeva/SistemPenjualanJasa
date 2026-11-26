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
        $usersByRole = User::query()
            ->selectRaw('role, COUNT(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        $sellerStats = SellerProfile::query()
            ->selectRaw('COUNT(CASE WHEN is_verified = false THEN 1 END) as sellers_pending, COALESCE(SUM(escrow_balance), 0) as escrow_total')
            ->first();

        $ordersByStatus = Order::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->orderBy('status')
            ->pluck('aggregate', 'status');

        $withdrawalsByStatus = Withdrawal::query()
            ->selectRaw('status, COUNT(*) as count, COALESCE(SUM(amount), 0) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $stats = [
            'users'               => $usersByRole->sum(),
            'buyers'              => (int) $usersByRole->get('buyer', 0),
            'sellers'             => (int) $usersByRole->get('seller', 0),
            'admins'              => (int) $usersByRole->get('admin', 0),
            'sellers_pending'     => (int) $sellerStats->sellers_pending,
            'services'            => Service::count(),
            'orders'              => $ordersByStatus->sum(),
            'orders_by_status'    => $ordersByStatus,
            'escrow_total'        => (float) $sellerStats->escrow_total,
            'withdrawals_pending' => (int) ($withdrawalsByStatus->get('pending')->count ?? 0),
            'withdrawals_total'   => (float) $withdrawalsByStatus->sum('total'),
            'withdrawals_paid'    => (float) ($withdrawalsByStatus->get('approved')->total ?? 0),
            'platform_revenue'    => (float) Order::where('status', 'completed')->sum('platform_fee'),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
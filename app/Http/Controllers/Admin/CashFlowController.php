<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : null;
        $to = isset($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : null;

        $paymentsQuery = Payment::query()
            ->where('status', 'paid')
            ->with([
                'order.buyer:id,name',
                'order.servicePackage.service.seller:id,name',
            ])
            ->when($from, fn (Builder $query) => $query->whereRaw('COALESCE(paid_at, created_at) >= ?', [$from]))
            ->when($to, fn (Builder $query) => $query->whereRaw('COALESCE(paid_at, created_at) <= ?', [$to]))
            ->orderByRaw('COALESCE(paid_at, created_at) DESC')
            ->orderByDesc('id');

        $withdrawalsQuery = Withdrawal::query()
            ->with('seller:id,name')
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->where('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        $incomeTotal = (clone $paymentsQuery)->sum('amount');
        $expenseTotal = (clone $withdrawalsQuery)->where('status', 'approved')->sum('amount');
        $payments = $paymentsQuery->paginate(15, ['*'], 'payments_page')->withQueryString();
        $withdrawals = $withdrawalsQuery->paginate(15, ['*'], 'withdrawals_page')->withQueryString();

        return view('admin.cash-flow.index', compact(
            'payments',
            'withdrawals',
            'from',
            'to',
            'incomeTotal',
            'expenseTotal',
        ));
    }
}

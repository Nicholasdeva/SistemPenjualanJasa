<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use DomainException;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with(['seller', 'admin'])->orderByDesc('created_at')->paginate(15);
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    /** Setujui penarikan (saldo penjual sudah dipotong saat pengajuan). */
    public function process(Request $request, Withdrawal $withdrawal, WithdrawalService $withdrawalService)
    {
        try {
            $withdrawalService->approve($withdrawal, $request->user());
        } catch (DomainException $e) {
            return back()->with('warning', $e->getMessage());
        }

        return back()->with('success', 'Permintaan penarikan dana disetujui.');
    }

    /** Tolak penarikan dan kembalikan nominalnya ke wallet penjual. */
    public function reject(Request $request, Withdrawal $withdrawal, WithdrawalService $withdrawalService)
    {
        try {
            $withdrawalService->reject($withdrawal, $request->user());
        } catch (DomainException $e) {
            return back()->with('warning', $e->getMessage());
        }

        return back()->with('success', 'Permintaan penarikan ditolak. Saldo dikembalikan ke penjual.');
    }
}

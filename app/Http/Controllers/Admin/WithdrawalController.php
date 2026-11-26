<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use DomainException;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with('seller')->orderByDesc('created_at')->get();
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    /** Setujui penarikan (saldo penjual sudah dipotong saat pengajuan). */
    public function process(Withdrawal $withdrawal, WithdrawalService $withdrawalService)
    {
        try {
            $withdrawalService->approve($withdrawal);
        } catch (DomainException $e) {
            return back()->with('warning', $e->getMessage());
        }

        return back()->with('success', 'Permintaan penarikan dana disetujui.');
    }

    /** Tolak penarikan dan kembalikan nominalnya ke wallet penjual. */
    public function reject(Withdrawal $withdrawal, WithdrawalService $withdrawalService)
    {
        try {
            $withdrawalService->reject($withdrawal);
        } catch (DomainException $e) {
            return back()->with('warning', $e->getMessage());
        }

        return back()->with('success', 'Permintaan penarikan ditolak. Saldo dikembalikan ke penjual.');
    }
}

<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::where('seller_id', auth()->id())->latest()->get();
        $wallet = auth()->user()->sellerProfile->wallet_balance ?? 0;
        $pendingTotal = $withdrawals->where('status', 'pending')->sum('amount');

        return view('seller.withdrawals', compact('withdrawals', 'wallet', 'pendingTotal'));
    }

    public function store(Request $request, WithdrawalService $withdrawalService)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:' . WithdrawalService::MIN_AMOUNT],
        ], [
            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric'  => 'Nominal harus berupa angka.',
            'amount.min'      => 'Minimal penarikan Rp 10.000.',
        ]);

        // Saldo wallet langsung dipotong; dikembalikan otomatis bila admin menolak.
        $withdrawalService->request($request->user(), (float) $data['amount']);

        return redirect()->route('seller.withdrawals.index')
            ->with('status', 'Permintaan penarikan diajukan. Saldo sudah dipotong dan menunggu diproses admin.');
    }
}

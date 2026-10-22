<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::where('seller_id', auth()->id())->latest()->get();
        $wallet = auth()->user()->sellerProfile->wallet_balance ?? 0;

        return view('seller.withdrawals', compact('withdrawals', 'wallet'));
    }

    // Tahap 1: pencatatan sederhana. Potong saldo + WithdrawalService dikerjakan di Tahap 3.
    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
        ], [
            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric'  => 'Nominal harus berupa angka.',
            'amount.min'      => 'Minimal penarikan Rp 10.000.',
        ]);

        $profile = auth()->user()->sellerProfile;

        if (! $profile || ! $profile->bank_account) {
            return back()->withErrors(['amount' => 'Lengkapi nomor rekening di Profil Penjual terlebih dahulu.']);
        }

        if ((float) $data['amount'] > (float) $profile->wallet_balance) {
            return back()->withErrors(['amount' => 'Nominal melebihi saldo tersedia.']);
        }

        Withdrawal::create([
            'seller_id'    => auth()->id(),
            'amount'       => $data['amount'],
            'bank_account' => $profile->bank_account,
            'status'       => 'pending',
        ]);

        return redirect()->route('seller.withdrawals.index')
            ->with('status', 'Permintaan penarikan dicatat dan menunggu diproses admin.');
    }
}
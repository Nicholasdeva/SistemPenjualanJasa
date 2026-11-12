<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with('seller')->orderByDesc('created_at')->get();
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function process(Withdrawal $withdrawal)
    {
        // Tahap 1: hanya ubah status. Logika saldo (WithdrawalService) dikerjakan di Tahap 3.
        // Nilai status mengikuti enum di migration: pending | approved | rejected
        if ($withdrawal->status !== 'pending') {
            return back()->with('warning', 'Permintaan ini sudah diproses sebelumnya.');
        }

        $withdrawal->status = 'approved';
        $withdrawal->save();

        return back()->with('success', 'Permintaan penarikan dana berhasil diproses.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = Withdrawal::with('user')->orderByDesc('created_at')->get();
        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function process(Withdrawal $withdrawal)
    {
        $withdrawal->status = 'processed';
        $withdrawal->save();

        return back()->with('success', 'Permintaan penarikan dana berhasil diproses.');
    }
}
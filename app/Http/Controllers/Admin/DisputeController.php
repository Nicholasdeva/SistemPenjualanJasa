<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index()
    {
        $disputes = Dispute::orderByDesc('created_at')->get();
        return view('admin.disputes.index', compact('disputes'));
    }

    public function show(Dispute $dispute)
    {
        return view('admin.disputes.show', compact('dispute'));
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $dispute->status = 'resolved';
        $dispute->resolution = $request->input('resolution'); // 'refund' atau 'release'
        $dispute->save();

        return redirect()->route('admin.disputes.index')->with('success', 'Sengketa berhasil diputuskan.');
    }
}
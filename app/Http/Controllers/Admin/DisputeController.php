<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index()
    {
        $disputes = Dispute::with(['order', 'raiser'])->orderByDesc('created_at')->get();
        return view('admin.disputes.index', compact('disputes'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load(['raiser', 'order.buyer', 'order.servicePackage.service.seller']);

        return view('admin.disputes.show', compact('dispute'));
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $data = $request->validate([
            'resolution'     => ['required', 'in:refund,release'],
            'admin_decision' => ['nullable', 'string', 'max:1000'],
        ], [
            'resolution.required' => 'Pilih putusan terlebih dahulu.',
            'resolution.in'       => 'Putusan tidak valid.',
        ]);

        if ($dispute->status !== 'open') {
            return back()->with('warning', 'Sengketa ini sudah diputuskan.');
        }

        // Tahap 1: hanya mencatat putusan. Pemindahan dana (refund / EscrowService) dikerjakan di Tahap 3.
        // Nilai status mengikuti enum di migration.
        $dispute->status = $data['resolution'] === 'refund' ? 'resolved_buyer_refund' : 'resolved_seller_payout';
        $dispute->admin_decision = $data['admin_decision'] ?? null;
        $dispute->save();

        return redirect()->route('admin.disputes.index')->with('success', 'Sengketa berhasil diputuskan.');
    }
}

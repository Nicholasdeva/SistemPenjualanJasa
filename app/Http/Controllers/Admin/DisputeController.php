<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Services\EscrowService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function resolve(Request $request, Dispute $dispute, EscrowService $escrow)
    {
        $data = $request->validate([
            'resolution'     => ['required', 'in:refund,release'],
            'admin_decision' => ['nullable', 'string', 'max:1000'],
        ], [
            'resolution.required' => 'Pilih putusan terlebih dahulu.',
            'resolution.in'       => 'Putusan tidak valid.',
        ]);

        try {
            DB::transaction(function () use ($dispute, $data, $escrow) {
                $locked = Dispute::whereKey($dispute->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== 'open') {
                    throw new DomainException('Sengketa ini sudah diputuskan.');
                }

                // Pindahkan dana escrow sesuai putusan: refund -> kembali ke pembeli, release -> ke wallet penjual.
                $data['resolution'] === 'refund'
                    ? $escrow->refund($locked->order)
                    : $escrow->release($locked->order);

                // Nilai status mengikuti enum di migration.
                $locked->status = $data['resolution'] === 'refund' ? 'resolved_buyer_refund' : 'resolved_seller_payout';
                $locked->admin_decision = $data['admin_decision'] ?? null;
                $locked->save();
            });
        } catch (DomainException $e) {
            return back()->with('warning', $e->getMessage());
        }

        return redirect()->route('admin.disputes.index')->with('success', 'Sengketa berhasil diputuskan.');
    }
}

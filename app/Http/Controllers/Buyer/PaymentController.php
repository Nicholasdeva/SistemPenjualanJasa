<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransPaymentService;

class PaymentController extends Controller
{
    public function __construct(private MidtransPaymentService $midtrans)
    {
    }

    /** Halaman bayar: membuka popup Midtrans Snap. */
    public function pay(Order $order)
    {
        $this->authorizeOrder($order);

        if ($order->status !== 'pending_payment') {
            return redirect()
                ->route('buyer.orders.index')
                ->with('status', "Pesanan {$order->invoice_no} tidak sedang menunggu pembayaran.");
        }

        $order->load('servicePackage.service.seller');

        return view('buyer.orders.pay', compact('order'));
    }

    /** Dipanggil JavaScript: membuat Snap token. */
    public function snapToken(Order $order)
    {
        $this->authorizeOrder($order);

        if ($order->status !== 'pending_payment') {
            return response()->json(['message' => 'Pesanan ini tidak menunggu pembayaran.'], 422);
        }

        try {
            $payment = $this->midtrans->createSnapPayment($order);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Gagal membuat transaksi Midtrans: ' . $e->getMessage()], 500);
        }

        return response()->json(['token' => $payment->snap_token]);
    }

    /** Dipanggil setelah popup selesai: cek status ke Midtrans (tidak butuh webhook/ngrok). */
    public function sync(Order $order)
    {
        $this->authorizeOrder($order);

        $result  = 'pending';
        $payment = $order->payments()->where('payment_method', 'midtrans')->latest('id')->first();

        if ($payment) {
            try {
                $result = $this->midtrans->syncFromMidtrans($payment);
            } catch (\Throwable $e) {
                report($e); // transaksi belum dibuat di Midtrans (popup ditutup sebelum memilih metode)
            }
        }

        $order->refresh();

        if ($order->status !== 'pending_payment') {
            return response()->json(['status' => 'paid', 'redirect' => route('workspace.show', $order->id)]);
        }

        return response()->json(['status' => $result, 'redirect' => route('buyer.orders.index')]);
    }

    /** Tombol demo: hanya APP_ENV=local. */
    public function simulatePay(Order $order)
    {
        abort_unless(app()->environment('local'), 404);
        $this->authorizeOrder($order);

        if ($order->status !== 'pending_payment') {
            return redirect()->route('buyer.orders.index');
        }

        $this->midtrans->simulatePaid($order);

        return redirect()
            ->route('workspace.show', $order->id)
            ->with('status', "Pembayaran {$order->invoice_no} disimulasikan (mode lokal). Dana masuk escrow.");
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless((int) $order->buyer_id === (int) auth()->id(), 403);
    }
}
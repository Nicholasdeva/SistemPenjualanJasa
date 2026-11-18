<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\SellerProfile;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use RuntimeException;

class MidtransPaymentService
{
    /** Snap token berlaku 24 jam; token yang masih baru dipakai ulang. */
    private const TOKEN_REUSE_HOURS = 20;

    public function __construct()
    {
        Config::$serverKey    = (string) config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    /**
     * Buat (atau pakai ulang) percobaan pembayaran + Snap token untuk sebuah pesanan.
     */
    public function createSnapPayment(Order $order): Payment
    {
        if (blank(Config::$serverKey)) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diisi di .env.');
        }

        $existing = $order->payments()
            ->where('payment_method', 'midtrans')
            ->where('status', 'pending')
            ->whereNotNull('snap_token')
            ->where('created_at', '>=', now()->subHours(self::TOKEN_REUSE_HOURS))
            ->latest('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        // Tiap percobaan dicatat di tabel payments.
        $payment = DB::transaction(function () use ($order) {
            $payment = Payment::create([
                'order_id'       => $order->id,
                'payment_method' => 'midtrans',
                'amount'         => $order->total_price,
                'status'         => 'pending',
            ]);
            $payment->update(['midtrans_order_id' => $order->invoice_no . '-' . $payment->id]);

            return $payment;
        });

        $order->loadMissing('buyer', 'servicePackage.service');
        $gross = (int) round((float) $order->total_price);

        $params = [
            'transaction_details' => [
                'order_id'     => $payment->midtrans_order_id,
                'gross_amount' => $gross,
            ],
            'item_details' => [[
                'id'       => 'PKG-' . $order->service_package_id,
                'price'    => $gross,
                'quantity' => 1,
                'name'     => Str::limit(
                    ($order->servicePackage->service->title ?? 'Jasa') . ' - ' . ($order->servicePackage->tier_name ?? ''),
                    50,
                    ''
                ),
            ]],
            'customer_details' => [
                'first_name' => $order->buyer->name,
                'email'      => $order->buyer->email,
            ],
        ];

        try {
            $token = Snap::getSnapToken($params);
        } catch (\Throwable $e) {
            $payment->update(['status' => 'failed']);
            throw $e;
        }

        $payment->update(['snap_token' => $token]);

        return $payment;
    }

    /** Cek signature_key notifikasi: sha512(order_id + status_code + gross_amount + server_key). */
    public function verifySignature(array $payload): bool
    {
        $expected = hash(
            'sha512',
            ($payload['order_id'] ?? '') . ($payload['status_code'] ?? '') . ($payload['gross_amount'] ?? '') . Config::$serverKey
        );

        return hash_equals($expected, (string) ($payload['signature_key'] ?? ''));
    }

    /** Dipakai webhook. Hasil: paid | pending | failed | invalid_signature | not_found | amount_mismatch */
    public function handleNotification(array $payload): string
    {
        if (! $this->verifySignature($payload)) {
            return 'invalid_signature';
        }

        $payment = Payment::where('midtrans_order_id', $payload['order_id'] ?? '')->first();
        if (! $payment) {
            return 'not_found';
        }

        return $this->applyStatus($payment, $payload);
    }

    /** Tanya status langsung ke API Midtrans (jalan di localhost tanpa webhook). */
    public function syncFromMidtrans(Payment $payment): string
    {
        $data = json_decode(json_encode(Transaction::status($payment->midtrans_order_id)), true);

        return $this->applyStatus($payment, $data ?? []);
    }

    /** Mode lokal saja: pura-pura bayar sukses, lewat jalur yang sama dengan pembayaran asli. */
    public function simulatePaid(Order $order): Payment
    {
        abort_unless(app()->environment('local'), 404);

        $payment = DB::transaction(function () use ($order) {
            $payment = Payment::create([
                'order_id'       => $order->id,
                'payment_method' => 'simulation',
                'amount'         => $order->total_price,
                'status'         => 'pending',
            ]);
            $payment->update(['midtrans_order_id' => $order->invoice_no . '-' . $payment->id]);

            return $payment;
        });

        $this->applyStatus($payment, [
            'transaction_status' => 'settlement',
            'gross_amount'       => (string) $order->total_price,
            'transaction_id'     => 'SIM-' . $payment->id,
            'payment_type'       => 'simulation',
        ]);

        return $payment->fresh();
    }

    private function applyStatus(Payment $payment, array $data): string
    {
        $order = $payment->order;

        // Nominal harus sama dengan orders.total_price.
        $gross = (int) round((float) ($data['gross_amount'] ?? 0));
        if ($gross !== (int) round((float) $order->total_price)) {
            Log::warning('Midtrans: nominal tidak cocok', [
                'payment_id' => $payment->id,
                'diterima'   => $gross,
                'seharusnya' => $order->total_price,
            ]);

            return 'amount_mismatch';
        }

        $transaction = $data['transaction_status'] ?? '';
        $fraud       = $data['fraud_status'] ?? null;

        $isPaid = $transaction === 'settlement'
            || ($transaction === 'capture' && in_array($fraud, [null, 'accept'], true));

        if ($isPaid) {
            $this->markPaid($payment, $data);

            return 'paid';
        }

        if (in_array($transaction, ['deny', 'cancel', 'expire', 'failure'], true)) {
            if ($payment->status === 'pending') {
                $payment->update([
                    'status'           => $transaction === 'expire' ? 'expired' : 'failed',
                    'transaction_id'   => $data['transaction_id'] ?? $payment->transaction_id,
                    'raw_notification' => $data,
                ]);
            }

            return 'failed';
        }

        // pending / challenge: catat saja.
        if ($payment->status === 'pending') {
            $payment->update([
                'payment_type'     => $data['payment_type'] ?? $payment->payment_type,
                'transaction_id'   => $data['transaction_id'] ?? $payment->transaction_id,
                'raw_notification' => $data,
            ]);
        }

        return 'pending';
    }

    /**
     * Pembayaran sukses: pesanan -> paid, isi paid_at + deadline_at, escrow penjual bertambah,
     * Workspace dibuat. Idempoten: notifikasi ganda tidak menambah escrow dua kali.
     */
    private function markPaid(Payment $payment, array $data): void
    {
        DB::transaction(function () use ($payment, $data) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($payment->status === 'paid') {
                return;
            }

            $order  = Order::with('servicePackage.service')->whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $paidAt = now();

            $payment->update([
                'status'           => 'paid',
                'transaction_id'   => $data['transaction_id'] ?? $payment->transaction_id,
                'payment_type'     => $data['payment_type'] ?? $payment->payment_type,
                'paid_at'          => $paidAt,
                'raw_notification' => $data,
            ]);

            // Pesanan sudah diproses lewat percobaan lain: jangan menambah escrow dua kali.
            if ($order->status !== 'pending_payment') {
                Log::warning('Midtrans: pembayaran ganda untuk pesanan yang sudah dibayar', [
                    'order_id'   => $order->id,
                    'payment_id' => $payment->id,
                ]);

                return;
            }

            $order->update([
                'status'      => 'paid',
                'deadline_at' => $paidAt->copy()->addDays((int) $order->servicePackage->delivery_days),
            ]);

            // Keputusan #3: escrow_balance += total_price.
            $profile = SellerProfile::where('user_id', $order->servicePackage->service->seller_id)
                ->lockForUpdate()
                ->firstOrFail();
            $profile->increment('escrow_balance', (float) $order->total_price);

            Workspace::firstOrCreate(['order_id' => $order->id]);
        });
    }
}
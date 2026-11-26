<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SellerProfile;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Rekening bersama (escrow) antara pembeli dan penjual.
 *
 * Alur dana:
 *  - hold()    : pembayaran sukses  -> escrow_balance += total_price
 *  - release() : pesanan disetujui  -> escrow_balance -= total_price, wallet_balance += total_price - platform_fee
 *  - refund()  : sengketa dimenangkan pembeli -> escrow_balance -= total_price (dana kembali ke pembeli)
 *
 * Status pesanan dipakai sebagai penanda idempoten: dana hanya bisa dilepas/dikembalikan
 * selama pesanan masih berada di salah satu HELD_STATUSES, sehingga tidak bisa diproses dua kali.
 */
class EscrowService
{
    /** Status pesanan di mana dana pembeli sedang ditahan di escrow. */
    public const HELD_STATUSES = ['paid', 'in_progress', 'delivered', 'revision', 'disputed'];

    /**
     * Tahan dana pembayaran di escrow penjual. Dipanggil sekali saat pembayaran sukses,
     * di dalam transaksi yang sudah mengunci pesanan (lihat MidtransPaymentService::markPaid).
     */
    public function hold(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $this->lockSellerProfile($order)->increment('escrow_balance', (float) $order->total_price);
        });
    }

    /**
     * Lepas dana escrow ke wallet penjual (dipotong biaya platform) dan tandai pesanan selesai.
     */
    public function release(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order   = $this->lockHeldOrder($order);
            $profile = $this->lockSellerProfile($order);
            $total   = (float) $order->total_price;

            $this->assertEscrowCovers($profile, $total);

            $profile->escrow_balance = (float) $profile->escrow_balance - $total;
            $profile->wallet_balance = (float) $profile->wallet_balance + $this->sellerNet($order);
            $profile->save();

            $order->update(['status' => 'completed']);

            return $order;
        });
    }

    /**
     * Kembalikan dana escrow ke pembeli: escrow penjual berkurang, pesanan dibatalkan,
     * dan pembayaran yang sudah lunas ditandai refunded.
     */
    public function refund(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order   = $this->lockHeldOrder($order);
            $profile = $this->lockSellerProfile($order);
            $total   = (float) $order->total_price;

            $this->assertEscrowCovers($profile, $total);

            $profile->decrement('escrow_balance', $total);

            $order->update(['status' => 'cancelled']);
            $order->payments()->where('status', 'paid')->update(['status' => 'refunded']);

            return $order;
        });
    }

    /** Pendapatan bersih penjual dari satu pesanan. */
    public function sellerNet(Order $order): float
    {
        return round((float) $order->total_price - (float) $order->platform_fee, 2);
    }

    private function lockHeldOrder(Order $order): Order
    {
        $locked = Order::with('servicePackage.service')
            ->whereKey($order->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (! in_array($locked->status, self::HELD_STATUSES, true)) {
            throw new DomainException(
                "Dana pesanan {$locked->invoice_no} tidak sedang ditahan di escrow (status: {$locked->status})."
            );
        }

        return $locked;
    }

    private function lockSellerProfile(Order $order): SellerProfile
    {
        $order->loadMissing('servicePackage.service');
        $sellerId = $order->servicePackage?->service?->seller_id;

        if (! $sellerId) {
            throw new DomainException("Penjual untuk pesanan {$order->invoice_no} tidak ditemukan.");
        }

        return SellerProfile::where('user_id', $sellerId)->lockForUpdate()->firstOrFail();
    }

    private function assertEscrowCovers(SellerProfile $profile, float $amount): void
    {
        if (round((float) $profile->escrow_balance, 2) < round($amount, 2)) {
            throw new DomainException('Saldo escrow penjual tidak mencukupi untuk pesanan ini.');
        }
    }
}

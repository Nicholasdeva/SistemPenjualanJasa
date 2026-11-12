<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Hanya pembeli dan penjual dari pesanan ini yang boleh membukanya.
     * Tabel orders tidak punya seller_id, jadi penjual diambil lewat paket -> jasa.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id || $this->isSeller($user, $order);
    }

    /**
     * Penjual mulai mengerjakan pesanan: hanya pemilik jasa, dan hanya saat pesanan sudah dibayar.
     */
    public function start(User $user, Order $order): bool
    {
        return $this->isSeller($user, $order) && $order->status === 'paid';
    }

    private function isSeller(User $user, Order $order): bool
    {
        $order->loadMissing('servicePackage.service');

        return $user->id === $order->servicePackage?->service?->seller_id;
    }
}

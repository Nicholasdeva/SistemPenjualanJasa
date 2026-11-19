<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Service;

class DashboardController extends Controller
{
    /** Status menunggu pembayaran (mendukung pending_payment maupun pending dari seeder lama). */
    private const PENDING_STATUSES = ['pending_payment', 'pending'];

    /** Status pesanan yang masih berjalan (belum selesai/batal). */
    private const ACTIVE_STATUSES = ['pending_payment', 'pending', 'paid', 'in_progress', 'delivered', 'revision'];

    /** Status saat dana pembeli masih ditahan di escrow. */
    private const ESCROW_STATUSES = ['paid', 'in_progress', 'delivered', 'revision', 'disputed'];

    public function index()
    {
        $buyerId = auth()->id();

        $stats = [
            'pending_payment'  => Order::where('buyer_id', $buyerId)->whereIn('status', self::PENDING_STATUSES)->count(),
            'active_orders'    => Order::where('buyer_id', $buyerId)->whereIn('status', ['paid', 'in_progress', 'revision'])->count(),
            'waiting_approval' => Order::where('buyer_id', $buyerId)->where('status', 'delivered')->count(),
            'completed_orders' => Order::where('buyer_id', $buyerId)->where('status', 'completed')->count(),
            'escrow_protected' => (float) Order::where('buyer_id', $buyerId)->whereIn('status', self::ESCROW_STATUSES)->sum('total_price'),
        ];

        $activeOrders = Order::with('servicePackage.service.seller')
            ->where('buyer_id', $buyerId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Order $o) => [
                'id'            => $o->id,
                'invoice_no'    => $o->invoice_no,
                'service'       => $o->servicePackage->service->title ?? 'Jasa',
                'seller_name'   => $o->servicePackage->service->seller->name ?? '-',
                'tier_name'     => $o->servicePackage->tier_name ?? '-',
                'total_price'   => (float) $o->total_price,
                'status'        => $o->status,
                'is_pending'    => in_array($o->status, self::PENDING_STATUSES, true),
                'is_overdue'    => $o->isOverdue(),
                'deadline'      => $o->deadline_at?->format('d M Y, H:i') ?? 'Dimulai setelah dibayar',
                'progress_note' => $this->progressNote($o->status),
            ])
            ->all();

        $recentHistory = Order::with(['servicePackage.service.seller', 'review'])
            ->where('buyer_id', $buyerId)
            ->where('status', 'completed')
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn (Order $o) => [
                'id'           => $o->id,
                'invoice_no'   => $o->invoice_no,
                'service'      => $o->servicePackage->service->title ?? 'Jasa',
                'seller_name'  => $o->servicePackage->service->seller->name ?? '-',
                'total_price'  => (float) $o->total_price,
                'status'       => $o->status,
                'completed_at' => $o->updated_at->format('d M Y'),
                'has_review'   => $o->review !== null,
                'rating'       => $o->review?->rating,
            ])
            ->all();

        // Rekomendasi: jasa terbaru yang tampil di katalog (jasa tersimpan/wishlist belum ada di database).
        $savedServices = Service::visibleInCatalog()
            ->with(['seller', 'category', 'packages'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn (Service $s) => [
                'id'             => $s->id,
                'title'          => $s->title,
                'category'       => $s->category?->name ?? '-',
                'seller'         => $s->seller->name ?? '-',
                'rating'         => round((float) $s->reviews_avg_rating, 1),
                'reviews_count'  => $s->reviews_count,
                'starting_price' => (int) round((float) $s->packages->min('price')),
                'delivery_days'  => (int) $s->packages->min('delivery_days'),
            ])
            ->all();

        return view('buyer.dashboard', compact('stats', 'activeOrders', 'recentHistory', 'savedServices'));
    }

    private function progressNote(string $status): string
    {
        return match ($status) {
            'pending_payment', 'pending' => 'Menunggu pembayaran. Selesaikan pembayaran agar penjual mulai bekerja.',
            'paid'                       => 'Pembayaran diterima. Menunggu penjual memulai pengerjaan.',
            'in_progress'                => 'Penjual sedang mengerjakan pesanan Anda.',
            'delivered'                  => 'Hasil kerja sudah diunggah. Menunggu review dan persetujuan Anda.',
            'revision'                   => 'Permintaan revisi terkirim. Menunggu penjual mengunggah perbaikan.',
            default                      => '',
        };
    }
}
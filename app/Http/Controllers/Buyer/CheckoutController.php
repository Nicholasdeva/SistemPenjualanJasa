<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /** Biaya platform: 10% dari harga paket, dipotong dari penjual (keputusan #2). */
    private const PLATFORM_FEE_RATE = 0.10;

    public function show(ServicePackage $package)
    {
        $package->load('service.seller.sellerProfile');
        $serviceModel = $package->service;

        // Jasa yang tidak tampil di katalog (draft / penjual belum terverifikasi) tidak bisa dipesan.
        if (! $serviceModel || ! Service::visibleInCatalog()->whereKey($serviceModel->id)->exists()) {
            return redirect()
                ->route('catalog')
                ->with('error', 'Jasa ini sedang tidak tersedia untuk dipesan.');
        }

        abort_if((int) $serviceModel->seller_id === (int) auth()->id(), 403, 'Anda tidak dapat memesan jasa milik sendiri.');

        $packages = $serviceModel->packages()
            ->orderBy('price')
            ->get()
            ->mapWithKeys(fn (ServicePackage $p) => [$p->id => $this->packageToArray($p)])
            ->all();

        $selectedPackage = $packages[$package->id];

        $totalPrice  = $selectedPackage['price'];
        $platformFee = (int) round($totalPrice * self::PLATFORM_FEE_RATE);

        $service = [
            'id'          => $serviceModel->id,
            'title'       => $serviceModel->title,
            'category'    => $serviceModel->category?->name ?? '-',
            'seller_name' => $serviceModel->seller->name,
            'is_verified' => (bool) $serviceModel->seller->sellerProfile?->is_verified,
            'rating'      => round((float) Review::where('service_id', $serviceModel->id)->avg('rating'), 1),
            'reviews'     => Review::where('service_id', $serviceModel->id)->count(),
        ];

        return view('buyer.checkout', compact(
            'service',
            'packages',
            'selectedPackage',
            'platformFee',
            'totalPrice'
        ));
    }

    public function store(StoreOrderRequest $request)
    {
        $package = ServicePackage::findOrFail($request->integer('service_package_id'));

        $brief = trim($request->input('project_brief'));
        if ($request->filled('reference_link')) {
            $brief .= "\n\nReferensi: " . $request->input('reference_link');
        }

        $order = DB::transaction(function () use ($request, $package, $brief) {
            $price = (float) $package->price;

            return Order::create([
                'invoice_no'         => $this->generateInvoiceNo(),
                'buyer_id'           => $request->user()->id,
                'service_package_id' => $package->id,
                'status'             => 'pending_payment',
                'project_brief'      => $brief,
                'total_price'        => $price,
                'platform_fee'       => round($price * self::PLATFORM_FEE_RATE, 2),
                'deadline_at'        => null,
            ]);
        });

            return redirect()
            ->route('buyer.orders.pay', $order->id)
            ->with('status', "Pesanan {$order->invoice_no} dibuat. Silakan selesaikan pembayaran.");
    }

    private function generateInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';

        $last = Order::where('invoice_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('invoice_no')
            ->value('invoice_no');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function packageToArray(ServicePackage $p): array
    {
        return [
            'id'             => $p->id,
            'tier_name'      => $p->tier_name,
            'price'          => (int) round((float) $p->price),
            'delivery_days'  => (int) $p->delivery_days,
            'revision_limit' => (int) $p->revision_limit,
            'features'       => [
                "Pengerjaan {$p->delivery_days} hari kerja",
                "Maksimal {$p->revision_limit}x revisi",
            ],
        ];
    }
}
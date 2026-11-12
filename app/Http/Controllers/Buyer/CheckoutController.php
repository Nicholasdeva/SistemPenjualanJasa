<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function show(Request $request, ?int $package = null)
    {
        if (! $package) {
            return redirect()->route('catalog')
                ->with('info', 'Pilih jasa dan paket yang ingin dipesan dari katalog.');
        }

        $selectedPackageModel = ServicePackage::with([
            'service.category',
            'service.seller.sellerProfile',
        ])->findOrFail($package);
        $service = $selectedPackageModel->service;

        abort_unless(Service::visibleInCatalog()->whereKey($service->id)->exists(), 404);
        abort_if($service->seller_id === $request->user()->id, 403, 'Anda tidak dapat memesan jasa milik sendiri.');

        $service->load(['packages' => fn ($query) => $query->orderBy('price')])
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        $packages = $service->packages->keyBy('id')->map(fn (ServicePackage $servicePackage) => [
            'id' => $servicePackage->id,
            'tier_name' => $servicePackage->tier_name,
            'price' => $servicePackage->price,
            'delivery_days' => $servicePackage->delivery_days,
            'revision_limit' => $servicePackage->revision_limit,
        ])->all();
        $selectedPackage = $packages[$selectedPackageModel->id];
        $platformFee = (int) round($selectedPackage['price'] * 0.10);
        $totalPrice = $selectedPackage['price'] + $platformFee;

        $serviceSummary = [
            'id' => $service->id,
            'title' => $service->title,
            'category' => $service->category->name,
            'seller_name' => $service->seller->name,
            'is_verified' => (bool) $service->seller->sellerProfile?->is_verified,
            'rating' => $service->reviews_avg_rating ?? 0,
            'reviews' => $service->reviews_count,
        ];

        return view('buyer.checkout', [
            'service' => $serviceSummary,
            'packages' => $packages,
            'selectedPackage' => $selectedPackage,
            'platformFee' => $platformFee,
            'totalPrice' => $totalPrice,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_package_id' => ['required', 'integer', 'exists:service_packages,id'],
            'project_brief' => ['required', 'string', 'min:20'],
        ], [
            'project_brief.required' => 'Brief proyek wajib diisi agar penjual memahami kebutuhan Anda.',
            'project_brief.min' => 'Brief proyek minimal 20 karakter agar instruksi jelas.',
        ]);

        $package = ServicePackage::with('service')->findOrFail($data['service_package_id']);
        abort_unless(Service::visibleInCatalog()->whereKey($package->service_id)->exists(), 404);
        abort_if($package->service->seller_id === $request->user()->id, 403, 'Anda tidak dapat memesan jasa milik sendiri.');

        return redirect()
            ->route('workspace.show', 1)
            ->with('status', 'Pesanan dummy berhasil dibuat! Pada Tahap 2 tombol ini akan membuka popup pembayaran Midtrans Snap Sandbox.');
    }
}

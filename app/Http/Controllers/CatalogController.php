<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;

class CatalogController extends Controller
{/*  */
    // menampilkan grid jasa yang tayang
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        $services = Service::query()
            ->visibleInCatalog()
            ->with(['seller:id,name', 'category:id,name', 'packages' => fn ($q) => $q->orderBy('price')])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->get();

        return view('public.catalog', compact('categories', 'services'));
    }

    public function show(Service $service)
    {
        // Jasa draft atau milik penjual yang belum terverifikasi tidak bisa dibuka publik
        abort_unless(Service::visibleInCatalog()->whereKey($service->id)->exists(), 404);

        $service->load([
            'seller.sellerProfile',
            'category',
            'packages' => fn ($q) => $q->orderBy('price'),
            'reviews'  => fn ($q) => $q->with('order.buyer:id,name')->latest(),
        ]);

        $sellerStats = [
            'services' => Service::visibleInCatalog()->where('seller_id', $service->seller_id)->count(),
            'joined'   => $service->seller->created_at,
        ];

        return view('public.service-detail', compact('service', 'sellerStats'));
    }
}

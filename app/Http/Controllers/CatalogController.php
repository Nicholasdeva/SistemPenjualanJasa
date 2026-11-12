<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', Rule::in(['latest', 'price_asc', 'price_desc'])],
        ]);

        $categories = Category::orderBy('name')->get();

        $query = Service::query()
            ->visibleInCatalog()
            ->with(['seller:id,name', 'category:id,name', 'packages' => fn ($q) => $q->orderBy('price')])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withMin('packages', 'price');

        $search = trim($filters['q'] ?? '');
        if ($search !== '') {
            $query->where('title', 'ILIKE', '%'.$search.'%');
        }

        if (! empty($filters['category'])) {
            $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $filters['category']));
        }

        match ($filters['sort'] ?? 'latest') {
            'price_asc' => $query->orderByRaw('packages_min_price ASC NULLS LAST')->latest(),
            'price_desc' => $query->orderByRaw('packages_min_price DESC NULLS LAST')->latest(),
            default => $query->latest(),
        };

        $services = $query->paginate(12)->withQueryString();

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
            'reviews' => fn ($q) => $q->with('order.buyer:id,name')->latest(),
        ]);

        $sellerStats = [
            'services' => Service::visibleInCatalog()->where('seller_id', $service->seller_id)->count(),
            'joined' => $service->seller->created_at,
        ];

        return view('public.service-detail', compact('service', 'sellerStats'));
    }
}

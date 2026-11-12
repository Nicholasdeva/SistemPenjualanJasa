<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\Service;
use Throwable;

class HomeController extends Controller
{
    public function index()
    {
        $categories = collect();
        $services = collect();
        $stats = ['services' => 0, 'sellers' => 0, 'completed' => 0, 'rating' => null];

        try {
            $categories = Category::query()
                ->withCount(['services' => fn ($q) => $q->visibleInCatalog()])
                ->orderByDesc('services_count')
                ->orderBy('name')
                ->take(8)
                ->get();

            $services = Service::query()
                ->visibleInCatalog()
                ->with(['seller:id,name', 'category:id,name', 'packages' => fn ($q) => $q->orderBy('price')])
                ->withAvg('reviews', 'rating')
                ->withCount('reviews')
                ->latest()
                ->take(6)
                ->get();

            $stats = [
                'services'  => Service::visibleInCatalog()->count(),
                'sellers'   => SellerProfile::where('is_verified', true)->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'rating'    => Review::avg('rating'),
            ];
        } catch (Throwable $e) {
            // Tabel belum dimigrasi — halaman tetap tampil dengan konten default.
            report($e);
        }

        return view('welcome', compact('categories', 'services', 'stats'));
    }
}

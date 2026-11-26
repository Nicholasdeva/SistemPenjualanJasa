<?php

namespace App\Providers;

use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\Service;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Data pratinjau untuk sisi kiri halaman login/register.
        View::composer('layouts.auth', function ($view) {
            $services = collect();
            $stats = ['sellers' => 0, 'rating' => null];

            try {
                $services = Service::query()
                    ->visibleInCatalog()
                    ->with(['seller:id,name', 'category:id,name', 'packages' => fn ($q) => $q->orderBy('price')])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->latest()
                    ->take(2)
                    ->get();

                $stats = [
                    'sellers' => SellerProfile::where('is_verified', true)->count(),
                    'rating'  => Review::avg('rating'),
                ];
            } catch (Throwable $e) {
                report($e);
            }

            $view->with('previewServices', $services)->with('previewStats', $stats);
        });
    }
}

<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_filters_by_category_and_sorts_by_cheapest_package_with_query_pagination(): void
    {
        $category = Category::create(['name' => 'Desain', 'slug' => 'desain']);
        $otherCategory = Category::create(['name' => 'Writing', 'slug' => 'writing']);
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller->id, 'is_verified' => true]);

        for ($index = 0; $index < 13; $index++) {
            $this->createService($seller, $category, 'Jasa '.str_pad((string) $index, 2, '0', STR_PAD_LEFT), 100000 + $index);
        }

        $this->createService($seller, $otherCategory, 'Jasa Kategori Lain', 50000);

        $response = $this->get(route('catalog', [
            'category' => $category->slug,
            'sort' => 'price_asc',
        ]));

        $response->assertOk()
            ->assertSeeInOrder(['Jasa 00', 'Jasa 01', 'Jasa 02'])
            ->assertDontSee('Jasa Kategori Lain')
            ->assertDontSee('Jasa 12')
            ->assertSee('category=desain')
            ->assertSee('sort=price_asc');

        $this->get(route('catalog', [
            'category' => $category->slug,
            'sort' => 'price_asc',
            'page' => 2,
        ]))->assertOk()->assertSee('Jasa 12');
    }

    public function test_catalog_search_uses_case_insensitive_matching_on_postgresql(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('PostgreSQL ILIKE behavior requires a PostgreSQL test connection.');
        }

        $category = Category::create(['name' => 'Desain', 'slug' => 'desain']);
        $seller = User::factory()->create(['role' => 'seller']);
        SellerProfile::create(['user_id' => $seller->id, 'is_verified' => true]);
        $this->createService($seller, $category, 'Logo Minimalis', 100000);
        $this->createService($seller, $category, 'Ilustrasi', 150000);

        $this->get(route('catalog', ['q' => 'LOGO']))
            ->assertOk()
            ->assertSee('Logo Minimalis')
            ->assertDontSee('Ilustrasi');
    }

    private function createService(User $seller, Category $category, string $title, int $price): Service
    {
        $service = Service::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => $title,
            'description' => 'Deskripsi jasa yang cukup panjang untuk pengujian katalog.',
            'status' => Service::STATUS_PUBLISHED,
        ]);

        $service->packages()->create([
            'tier_name' => 'Basic',
            'price' => $price,
            'delivery_days' => 3,
            'revision_limit' => 1,
        ]);

        return $service;
    }
}

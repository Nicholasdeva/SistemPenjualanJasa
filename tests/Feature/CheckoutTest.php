<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_displays_the_selected_package_and_database_service(): void
    {
        [$seller, $service, $package] = $this->createPublishedService();
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($buyer)
            ->get(route('buyer.checkout', $package))
            ->assertOk()
            ->assertSee($service->title)
            ->assertSee($seller->name)
            ->assertSee('Paket Basic')
            ->assertSee('100.000');
    }

    public function test_buyer_cannot_checkout_their_own_service(): void
    {
        [$seller, $service, $package] = $this->createPublishedService('buyer');

        $this->actingAs($seller)
            ->get(route('buyer.checkout', $package))
            ->assertForbidden();
    }

    private function createPublishedService(string $sellerRole = 'seller'): array
    {
        $seller = User::factory()->create(['role' => $sellerRole]);
        SellerProfile::create(['user_id' => $seller->id, 'is_verified' => true]);
        $category = Category::create(['name' => 'Desain', 'slug' => 'desain']);
        $service = Service::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'title' => 'Jasa Desain Logo',
            'description' => 'Deskripsi jasa yang cukup panjang untuk detail layanan.',
            'status' => Service::STATUS_PUBLISHED,
        ]);
        $package = ServicePackage::create([
            'service_id' => $service->id,
            'tier_name' => 'Basic',
            'price' => 100000,
            'delivery_days' => 3,
            'revision_limit' => 1,
        ]);

        return [$seller, $service, $package];
    }
}

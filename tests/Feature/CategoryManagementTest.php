<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_categories_with_unique_slugs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $this->post(route('admin.categories.store'), ['name' => 'Web Design']);
        $category = Category::where('name', 'Web Design')->firstOrFail();
        $this->assertSame('web-design', $category->slug);

        $this->post(route('admin.categories.store'), ['name' => 'Web-Design']);
        $duplicateSlugCategory = Category::where('name', 'Web-Design')->firstOrFail();
        $this->assertSame('web-design-2', $duplicateSlugCategory->slug);

        $this->put(route('admin.categories.update', $category), ['name' => 'Brand Design']);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Brand Design',
            'slug' => 'brand-design',
        ]);

        $this->delete(route('admin.categories.destroy', $duplicateSlugCategory));
        $this->assertDatabaseMissing('categories', ['id' => $duplicateSlugCategory->id]);
    }

    public function test_non_admin_cannot_access_category_management(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($user)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }
}

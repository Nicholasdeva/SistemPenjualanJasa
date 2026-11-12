<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_name_password_and_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nama Baru',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
            'avatar' => UploadedFile::fake()->image('avatar.png'),
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('status');

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertTrue(Hash::check('password-baru', $user->password));
        Storage::disk('public')->assertExists($user->avatar_url);
    }

    public function test_password_confirmation_and_avatar_type_are_validated(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($user)->from(route('profile.edit'))->put(route('profile.update'), [
            'name' => 'Nama Baru',
            'password' => 'password-baru',
            'password_confirmation' => 'tidak-sama',
            'avatar' => UploadedFile::fake()->create('avatar.gif', 10, 'image/gif'),
        ])->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors(['password', 'avatar']);

        $this->assertSame($user->name, $user->fresh()->name);
    }
}

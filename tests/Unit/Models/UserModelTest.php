<?php

namespace Tests\Unit\Models;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_helpers(): void
    {
        $this->assertTrue(User::factory()->admin()->make()->isAdmin());
        $this->assertFalse(User::factory()->admin()->make()->isMember());
        $this->assertTrue(User::factory()->make()->isMember());
        $this->assertFalse(User::factory()->make()->isAdmin());
    }

    public function test_has_many_posts(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(2)->for($user)->create();

        $this->assertCount(2, $user->posts);
    }

    public function test_password_is_hashed_and_hidden(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $this->assertNotSame('rahasia123', $user->password);
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_profile_photo_url_accessor(): void
    {
        Storage::fake('public');

        $this->assertNull(User::factory()->make()->profile_photo_url);
        $this->assertStringContainsString('p.png', User::factory()->make(['profile_photo_path' => 'profile-photos/p.png'])->profile_photo_url);
    }
}

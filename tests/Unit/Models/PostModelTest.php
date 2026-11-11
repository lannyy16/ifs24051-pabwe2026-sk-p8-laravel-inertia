<?php

namespace Tests\Unit\Models;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user_and_casts(): void
    {
        $post = Post::factory()->published()->create();

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_cover_url_accessor(): void
    {
        Storage::fake('public');

        $this->assertNull(Post::factory()->make()->cover_url);
        $this->assertStringContainsString('covers/a.jpg', Post::factory()->make(['cover_image_path' => 'covers/a.jpg'])->cover_url);
    }

    public function test_factory_states(): void
    {
        $this->assertFalse(Post::factory()->draft()->make()->is_published);
    }
}

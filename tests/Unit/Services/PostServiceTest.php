<?php

namespace Tests\Unit\Services;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = app(PostService::class);
    }

    public function test_make_slug_is_unique_and_has_fallback(): void
    {
        Post::factory()->create(['slug' => 'judul']);
        Post::factory()->create(['slug' => 'judul-2']);

        $this->assertSame('judul-3', $this->service->makeSlug('Judul'));
        $this->assertSame('judul', $this->service->makeSlug('Judul', Post::where('slug', 'judul')->value('id')));
        $this->assertSame('artikel', $this->service->makeSlug('???'));
    }

    public function test_create_normalises_unknown_category_and_publishes(): void
    {
        $user = User::factory()->create();

        $post = $this->service->create($user, ['title' => 'T', 'category' => 'ngawur', 'content' => 'c', 'is_published' => true]);

        $this->assertSame('Umum', $post->category);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_update_keeps_published_state_when_flag_missing_and_replaces_cover(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Lama', 'cover_image_path' => 'covers/old.jpg']);
        Storage::disk('public')->put('covers/old.jpg', 'x');

        $updated = $this->service->update($post, ['title' => 'Baru', 'content' => 'c'], UploadedFile::fake()->image('n.jpg'));

        $this->assertTrue($updated->is_published);
        $this->assertSame('baru', $updated->slug);
        Storage::disk('public')->assertMissing('covers/old.jpg');
        Storage::disk('public')->assertExists($updated->cover_image_path);
    }

    public function test_toggle_set_and_remove_cover_and_delete(): void
    {
        $post = Post::factory()->draft()->create();

        $this->assertTrue($this->service->togglePublish($post)->is_published);
        $this->assertFalse($this->service->togglePublish($post)->is_published);

        $withCover = $this->service->setCover($post, UploadedFile::fake()->image('a.jpg'));
        Storage::disk('public')->assertExists($withCover->cover_image_path);
        $path = $withCover->cover_image_path;

        $this->assertNull($this->service->removeCover($withCover)->cover_image_path);
        Storage::disk('public')->assertMissing($path);

        $this->service->delete($post);
        $this->assertModelMissing($post);
    }

    public function test_public_show_throws_when_not_found_and_related_lists(): void
    {
        $post = Post::factory()->published()->create();
        Post::factory()->published()->create();

        $this->assertTrue($this->service->publicShow($post->slug)->is($post));
        $this->assertCount(1, $this->service->related($post));
        $this->expectException(NotFoundHttpException::class);
        $this->service->publicShow('tidak-ada');
    }

    public function test_listing_stats_and_create_many(): void
    {
        $user = User::factory()->create();

        $count = $this->service->createMany($user, [
            ['title' => 'A', 'content' => 'a'],
            ['title' => 'B', 'content' => 'b', 'is_published' => true],
        ], false);

        $this->assertSame(2, $count);
        $this->assertSame(['total' => 2, 'published' => 1, 'draft' => 1], $this->service->stats($user));
        $this->assertSame(2, $this->service->listForUser($user, null)->total());
        $this->assertSame(2, $this->service->listAll(null)->total());
        $this->assertSame(1, $this->service->publicIndex(null, null)->total());
        $this->assertCount(2, $this->service->forExport($user->id));
    }
}

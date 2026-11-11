<?php

namespace Tests\Unit\Repositories;

use App\Features\Post\Models\Post;
use App\Features\Post\Repositories\PostRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PostRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new PostRepository;
    }

    public function test_paginate_published_filters_search_and_category(): void
    {
        Post::factory()->published()->create(['title' => 'Laravel Dasar', 'category' => 'Teknologi', 'excerpt' => null]);
        Post::factory()->published()->create(['title' => 'Karir', 'category' => 'Bisnis', 'excerpt' => 'tentang laravel']);
        Post::factory()->draft()->create(['title' => 'Draf Laravel']);

        $this->assertSame(2, $this->repo->paginatePublished('LARAVEL', null)->total());
        $this->assertSame(1, $this->repo->paginatePublished(null, 'Bisnis')->total());
        $this->assertSame(2, $this->repo->paginatePublished('  ', null)->total());
        $this->assertSame(0, $this->repo->paginatePublished('zzz', null)->total());
    }

    public function test_find_published_by_slug(): void
    {
        $published = Post::factory()->published()->create();
        $draft = Post::factory()->draft()->create();

        $this->assertTrue($this->repo->findPublishedBySlug($published->slug)->is($published));
        $this->assertNull($this->repo->findPublishedBySlug($draft->slug));
        $this->assertNull($this->repo->findPublishedBySlug('nope'));
    }

    public function test_related_published_prefers_same_category_and_excludes_self(): void
    {
        $post = Post::factory()->published()->create(['category' => 'Opini']);
        $same = Post::factory()->published()->create(['category' => 'Opini']);
        Post::factory()->published()->create(['category' => 'Bisnis']);
        Post::factory()->draft()->create(['category' => 'Opini']);

        $related = $this->repo->relatedPublished($post, 2);

        $this->assertCount(2, $related);
        $this->assertTrue($related->first()->is($same));
        $this->assertFalse($related->contains($post));
    }

    public function test_pagination_for_user_and_all(): void
    {
        $user = User::factory()->create();
        Post::factory()->count(3)->for($user)->create();
        Post::factory()->create(['title' => 'Lain Cari']);

        $this->assertSame(3, $this->repo->paginateForUser($user->id, null)->total());
        $this->assertSame(0, $this->repo->paginateForUser($user->id, 'cari')->total());
        $this->assertSame(4, $this->repo->paginateAll(null)->total());
        $this->assertSame(1, $this->repo->paginateAll('cari')->total());
    }

    public function test_all_for_export_and_stats(): void
    {
        $user = User::factory()->create();
        Post::factory()->published()->for($user)->create();
        Post::factory()->draft()->for($user)->create();
        Post::factory()->create();

        $this->assertCount(3, $this->repo->allForExport());
        $this->assertCount(2, $this->repo->allForExport($user->id));
        $this->assertSame(['total' => 2, 'published' => 1, 'draft' => 1], $this->repo->stats($user->id));
    }

    public function test_slug_exists_with_ignore(): void
    {
        $post = Post::factory()->create(['slug' => 'ada']);

        $this->assertTrue($this->repo->slugExists('ada'));
        $this->assertFalse($this->repo->slugExists('ada', $post->id));
        $this->assertFalse($this->repo->slugExists('tidak'));
    }

    public function test_create_update_delete(): void
    {
        $user = User::factory()->create();
        $post = $this->repo->create([
            'user_id' => $user->id, 'title' => 'T', 'slug' => 't', 'category' => 'Umum', 'content' => 'c',
        ]);
        $this->assertDatabaseHas('posts', ['slug' => 't']);

        $updated = $this->repo->update($post, ['title' => 'Baru']);
        $this->assertSame('Baru', $updated->title);

        $this->repo->delete($post);
        $this->assertModelMissing($post);
    }
}

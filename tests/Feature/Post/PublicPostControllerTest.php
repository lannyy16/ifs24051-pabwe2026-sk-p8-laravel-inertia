<?php

namespace Tests\Feature\Post;

use App\Features\Post\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicPostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_articles_list_only_published_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Terbit Satu']);
        Post::factory()->draft()->create(['title' => 'Draf Rahasia']);

        foreach (['/', '/articles'] as $url) {
            $this->get($url)->assertOk()->assertInertia(
                fn (Assert $p) => $p->component('Public/PostIndex')
                    ->has('posts.data', 1)
                    ->where('posts.data.0.title', 'Terbit Satu')
                    ->has('categories', 6)
            );
        }
    }

    public function test_search_and_category_filter(): void
    {
        Post::factory()->published()->create(['title' => 'Belajar Laravel', 'category' => 'Teknologi', 'content' => 'isi']);
        Post::factory()->published()->create(['title' => 'Tips Karir', 'category' => 'Bisnis', 'content' => 'isi', 'excerpt' => null]);

        $this->get('/articles?search=laravel')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
        $this->get('/articles?category=Bisnis')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1)->where('posts.data.0.title', 'Tips Karir'));
        $this->get('/articles?search=%20%20')->assertInertia(fn (Assert $p) => $p->has('posts.data', 2));
        $this->get('/articles?search=tidakada')->assertInertia(fn (Assert $p) => $p->has('posts.data', 0));
    }

    public function test_show_published_article_with_related(): void
    {
        $post = Post::factory()->published()->create(['category' => 'Opini']);
        Post::factory()->published()->count(2)->create(['category' => 'Opini']);

        $this->get('/articles/'.$post->slug)->assertOk()->assertInertia(
            fn (Assert $p) => $p->component('Public/PostShow')->where('post.id', $post->id)->has('related', 2)
        );
    }

    public function test_draft_or_missing_article_returns_404(): void
    {
        $draft = Post::factory()->draft()->create();

        $this->get('/articles/'.$draft->slug)->assertNotFound();
        $this->get('/articles/tidak-ada')->assertNotFound();
    }
}

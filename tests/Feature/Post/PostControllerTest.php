<?php

namespace Tests\Feature\Post;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->user = User::factory()->create();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'title' => 'Artikel Baru', 'category' => 'Teknologi', 'excerpt' => 'Ringkas',
            'content' => '## Halo', 'is_published' => true,
        ], $override);
    }

    // ---------------- CRUD ----------------

    public function test_index_shows_own_posts_with_stats_and_search(): void
    {
        Post::factory()->published()->for($this->user)->create(['title' => 'Milik Saya']);
        Post::factory()->draft()->for($this->user)->create();
        Post::factory()->create();

        $this->actingAs($this->user)->get('/dashboard/posts')->assertInertia(
            fn (Assert $p) => $p->component('Post/Index')
                ->has('posts.data', 2)
                ->where('stats', ['total' => 2, 'published' => 1, 'draft' => 1])
        );
        $this->actingAs($this->user)->get('/dashboard/posts?search=milik')
            ->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
    }

    public function test_store_creates_published_post_with_cover_and_unique_slug(): void
    {
        $this->actingAs($this->user)->post('/dashboard/posts', $this->payload(['cover' => UploadedFile::fake()->image('c.jpg')]))
            ->assertSessionHas('success');
        $this->actingAs($this->user)->post('/dashboard/posts', $this->payload(['is_published' => false]));

        $first = Post::orderBy('id')->first();
        $second = Post::orderByDesc('id')->first();

        $this->assertSame('artikel-baru', $first->slug);
        $this->assertSame('artikel-baru-2', $second->slug);
        $this->assertTrue($first->is_published);
        $this->assertNotNull($first->published_at);
        $this->assertFalse($second->is_published);
        $this->assertNull($second->published_at);
        Storage::disk('public')->assertExists($first->cover_image_path);
    }

    public function test_store_with_symbol_only_title_uses_fallback_slug(): void
    {
        $this->actingAs($this->user)->post('/dashboard/posts', $this->payload(['title' => '!!!']));

        $this->assertSame('artikel', Post::first()->slug);
    }

    public function test_store_validation(): void
    {
        $this->actingAs($this->user)->post('/dashboard/posts', ['category' => 'Salah'])
            ->assertSessionHasErrors(['title', 'category', 'content']);
    }

    public function test_show_own_post(): void
    {
        $post = Post::factory()->for($this->user)->create();

        $this->actingAs($this->user)->get('/dashboard/posts/'.$post->id)
            ->assertInertia(fn (Assert $p) => $p->component('Post/Show')->where('post.id', $post->id));
    }

    public function test_update_changes_slug_only_when_title_changes_and_replaces_cover(): void
    {
        $post = Post::factory()->published()->for($this->user)->create(['title' => 'Lama', 'slug' => 'lama', 'cover_image_path' => 'covers/old.jpg']);
        Storage::disk('public')->put('covers/old.jpg', 'x');
        $publishedAt = $post->published_at;

        $this->actingAs($this->user)->put('/dashboard/posts/'.$post->id, $this->payload(['title' => 'Lama', 'cover' => UploadedFile::fake()->image('n.jpg')]))
            ->assertSessionHas('success');
        $post->refresh();

        $this->assertSame('lama', $post->slug);
        $this->assertEquals($publishedAt, $post->published_at);
        Storage::disk('public')->assertMissing('covers/old.jpg');
        Storage::disk('public')->assertExists($post->cover_image_path);

        $this->actingAs($this->user)->put('/dashboard/posts/'.$post->id, $this->payload(['title' => 'Judul Berbeda']));
        $this->assertSame('judul-berbeda', $post->refresh()->slug);
    }

    public function test_update_can_unpublish_keep_and_publish(): void
    {
        $post = Post::factory()->published()->for($this->user)->create();

        $this->actingAs($this->user)->put('/dashboard/posts/'.$post->id, $this->payload(['title' => $post->title, 'is_published' => false]));
        $this->assertNull($post->refresh()->published_at);

        $this->actingAs($this->user)->put('/dashboard/posts/'.$post->id, array_diff_key($this->payload(['title' => $post->title]), ['is_published' => 1]));
        $this->assertFalse($post->refresh()->is_published);

        $this->actingAs($this->user)->put('/dashboard/posts/'.$post->id, $this->payload(['title' => $post->title, 'is_published' => true]));
        $this->assertNotNull($post->refresh()->published_at);
    }

    public function test_destroy_removes_post_and_cover(): void
    {
        $post = Post::factory()->for($this->user)->create(['cover_image_path' => 'covers/x.jpg']);
        Storage::disk('public')->put('covers/x.jpg', 'x');

        $this->actingAs($this->user)->delete('/dashboard/posts/'.$post->id)->assertRedirect('/dashboard/posts');

        $this->assertModelMissing($post);
        Storage::disk('public')->assertMissing('covers/x.jpg');
    }

    public function test_destroy_post_without_cover(): void
    {
        $post = Post::factory()->for($this->user)->create();

        $this->actingAs($this->user)->delete('/dashboard/posts/'.$post->id);

        $this->assertModelMissing($post);
    }

    public function test_toggle_publish(): void
    {
        $post = Post::factory()->draft()->for($this->user)->create();

        $this->actingAs($this->user)->patch("/dashboard/posts/{$post->id}/publish")->assertSessionHas('success', 'Artikel dipublikasikan.');
        $this->assertTrue($post->refresh()->is_published);

        $this->actingAs($this->user)->patch("/dashboard/posts/{$post->id}/publish")->assertSessionHas('success', 'Artikel dijadikan draf.');
        $this->assertFalse($post->refresh()->is_published);
    }

    public function test_cover_upload_and_removal(): void
    {
        $post = Post::factory()->for($this->user)->create();

        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('a.jpg')]);
        $first = $post->refresh()->cover_image_path;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", ['cover' => UploadedFile::fake()->image('b.jpg')]);
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($this->user)->post("/dashboard/posts/{$post->id}/cover", [])->assertSessionHasErrors('cover');

        $this->actingAs($this->user)->delete("/dashboard/posts/{$post->id}/cover");
        $this->assertNull($post->refresh()->cover_image_path);
    }

    public function test_cannot_touch_other_users_post(): void
    {
        $other = Post::factory()->create();

        $this->actingAs($this->user)->get('/dashboard/posts/'.$other->id)->assertForbidden();
        $this->actingAs($this->user)->delete('/dashboard/posts/'.$other->id)->assertForbidden();
        $this->assertModelExists($other);
    }

    // ---------------- Excel ----------------

    private function xlsx(array $rows): UploadedFile
    {
        $sheet = ($book = new Spreadsheet)->getActiveSheet();
        $sheet->fromArray($rows, null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'imp_').'.xlsx';
        (new Xlsx($book))->save($path);

        return new UploadedFile($path, 'artikel.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_member_exports_only_own_posts(): void
    {
        Post::factory()->published()->for($this->user)->create(['title' => 'Punya Saya']);
        Post::factory()->draft()->for($this->user)->create(['title' => 'Draf Saya']);
        Post::factory()->create(['title' => 'Orang Lain']);

        $response = $this->actingAs($this->user)->get('/dashboard/posts/export');
        $response->assertOk()->assertDownload('artikel-saya.xlsx');

        $sheet = IOFactory::load($response->baseResponse->getFile()->getPathname())->getActiveSheet()->toArray();
        $this->assertCount(3, $sheet);
        $this->assertSame('Punya Saya', $sheet[1][0]);
        $this->assertSame('Published', $sheet[1][4]);
        $this->assertSame('Draft', $sheet[2][4]);
    }

    public function test_member_imports_posts_and_skips_invalid_rows(): void
    {
        $file = $this->xlsx([
            ['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status'],
            ['Impor Satu', 'Bisnis', 'Ringkas', 'Isi satu', 'Published'],
            ['Impor Dua', 'Tidak Valid', '', 'Isi dua', 'draft'],
            ['', 'Umum', '', 'tanpa judul', ''],
            ['Tanpa Isi', 'Umum', '', '', ''],
            ['Impor Tiga', null, null, 'Isi tiga'],
        ]);

        $this->actingAs($this->user)->post('/dashboard/posts/import', ['file' => $file])
            ->assertSessionHas('success', '3 artikel diimpor, 2 baris dilewati.');

        $one = Post::where('title', 'Impor Satu')->first();
        $two = Post::where('title', 'Impor Dua')->first();
        $this->assertTrue($one->is_published);
        $this->assertSame('Bisnis', $one->category);
        $this->assertFalse($two->is_published);
        $this->assertSame('Umum', $two->category);
        $this->assertNull($two->excerpt);
        $this->assertSame(3, $this->user->posts()->count());
    }

    public function test_import_requires_excel_file(): void
    {
        $this->actingAs($this->user)
            ->post('/dashboard/posts/import', ['file' => UploadedFile::fake()->create('a.txt', 1, 'text/plain')])
            ->assertSessionHasErrors('file');
    }

    // ---------------- AI ----------------

    private function fakeOpenAi(mixed $content, int $status = 200): void
    {
        Http::fake(['*/chat/completions' => Http::response(
            ['choices' => [['message' => ['content' => is_string($content) ? $content : json_encode($content)]]]],
            $status,
        )]);
    }

    public function test_ai_generate_returns_items_limited_to_count(): void
    {
        config(['openai.key' => 'sk-test']);
        $this->fakeOpenAi(['posts' => [
            ['title' => ' Satu ', 'category' => 'Bisnis', 'excerpt' => 'R', 'content' => '## A'],
            ['title' => 'Dua', 'category' => 'ngawur', 'content' => 'B'],
        ]]);

        $this->actingAs($this->user)->postJson('/dashboard/posts/ai-generate', ['topic' => 'Laravel', 'count' => 1, 'category' => 'Bisnis'])
            ->assertOk()->assertJsonCount(1, 'items')->assertJsonPath('items.0.title', 'Satu');
    }

    public function test_ai_generate_reports_service_errors_as_422(): void
    {
        config(['openai.key' => '']);

        $this->actingAs($this->user)->postJson('/dashboard/posts/ai-generate', ['topic' => 'x', 'count' => 1])
            ->assertStatus(422)->assertJsonPath('message', 'OPENAI_KEY belum dikonfigurasi di server.');
    }

    public function test_ai_generate_validates_count_bounds(): void
    {
        $this->actingAs($this->user)->postJson('/dashboard/posts/ai-generate', ['topic' => 'x', 'count' => 0])->assertJsonValidationErrors('count');
        $this->actingAs($this->user)->postJson('/dashboard/posts/ai-generate', ['topic' => 'x', 'count' => 6])->assertJsonValidationErrors('count');
    }

    public function test_ai_save_batch(): void
    {
        $this->actingAs($this->user)->post('/dashboard/posts/ai-save', [
            'items' => [
                ['title' => 'AI Satu', 'category' => 'Teknologi', 'excerpt' => 'e', 'content' => 'c'],
                ['title' => 'AI Dua', 'content' => 'c'],
            ],
            'publish' => true,
        ])->assertSessionHas('success', '2 artikel hasil AI berhasil disimpan.');

        $this->assertSame(2, $this->user->posts()->where('is_published', true)->count());

        $this->actingAs($this->user)->post('/dashboard/posts/ai-save', ['items' => [['title' => 'Draf AI', 'content' => 'c']]]);
        $this->assertFalse($this->user->posts()->where('title', 'Draf AI')->first()->is_published);

        $this->actingAs($this->user)->post('/dashboard/posts/ai-save', ['items' => []])->assertSessionHasErrors('items');
    }
}

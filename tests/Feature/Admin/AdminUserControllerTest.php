<?php

namespace Tests\Feature\Admin;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_only_admin_can_access_admin_area(): void
    {
        $this->get('/admin/users')->assertRedirect('/login');
        $member = User::factory()->create();
        $this->actingAs($member)->get('/admin/users')->assertForbidden();
        $this->actingAs($member)->get('/admin/posts')->assertForbidden();
    }

    public function test_user_list_with_counts_and_search(): void
    {
        $writer = User::factory()->create(['name' => 'Penulis Hebat', 'username' => 'hebat']);
        Post::factory()->published()->for($writer)->create();
        Post::factory()->draft()->for($writer)->create();

        $this->actingAs($this->admin)->get('/admin/users')->assertInertia(
            fn (Assert $p) => $p->component('Admin/UserList')->has('users.data', 2)
        );
        $this->actingAs($this->admin)->get('/admin/users?search=HEBAT')->assertInertia(
            fn (Assert $p) => $p->has('users.data', 1)
                ->where('users.data.0.posts_count', 2)
                ->where('users.data.0.published_posts_count', 1)
        );
    }

    public function test_monitor_all_posts_and_user_posts_read_only(): void
    {
        $writer = User::factory()->create();
        Post::factory()->count(3)->for($writer)->create();
        $other = Post::factory()->create(['title' => 'Cari Aku']);

        $this->actingAs($this->admin)->get('/admin/posts')->assertInertia(
            fn (Assert $p) => $p->component('Admin/PostList')->has('posts.data', 4)
        );
        $this->actingAs($this->admin)->get('/admin/posts?search=cari')->assertInertia(fn (Assert $p) => $p->has('posts.data', 1));
        $this->actingAs($this->admin)->get("/admin/users/{$writer->id}/posts")->assertInertia(
            fn (Assert $p) => $p->component('Admin/UserPostList')->has('posts.data', 3)->where('stats.total', 3)->where('author.id', $writer->id)
        );

        // read-only: admin boleh melihat tetapi tidak boleh mengubah/menghapus artikel orang lain
        $this->actingAs($this->admin)->get("/dashboard/posts/{$other->id}")->assertOk();
        $this->actingAs($this->admin)->delete("/dashboard/posts/{$other->id}")->assertForbidden();
        $this->actingAs($this->admin)->put("/dashboard/posts/{$other->id}", ['title' => 'x', 'category' => 'Umum', 'content' => 'c'])->assertForbidden();
        $this->assertModelExists($other);
    }

    public function test_admin_exports_all_and_per_user(): void
    {
        $author = User::factory()->create(['username' => 'penulis']);
        Post::factory()->count(2)->for($author)->create();
        Post::factory()->create();

        $all = $this->actingAs($this->admin)->get('/admin/posts/export');
        $all->assertOk()->assertDownload('semua-artikel.xlsx');
        $this->assertCount(4, IOFactory::load($all->baseResponse->getFile()->getPathname())->getActiveSheet()->toArray());

        $one = $this->actingAs($this->admin)->get("/admin/users/{$author->id}/posts/export");
        $one->assertOk()->assertDownload('artikel-penulis.xlsx');
        $this->assertCount(3, IOFactory::load($one->baseResponse->getFile()->getPathname())->getActiveSheet()->toArray());
    }
}

<?php

namespace Tests\Feature;

use App\Features\Post\Models\Post;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_reachable_without_login(): void
    {
        foreach (['/', '/articles', '/login', '/register'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_dashboard_and_admin_require_authentication(): void
    {
        foreach (['/dashboard/posts', '/dashboard/posts/export', '/profile', '/admin/users', '/admin/posts'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_health_endpoint(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_shared_props_for_guest_and_user(): void
    {
        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('auth.user', null)->has('appName'));

        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile')->assertInertia(
            fn (Assert $p) => $p->where('auth.user.id', $user->id)->has('flash')
        );
    }

    public function test_seeder_is_idempotent_and_creates_known_accounts(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(User::where('username', 'admin')->first()->isAdmin());
        $this->assertTrue(User::where('username', 'member')->first()->isMember());
        $this->assertSame(6, Post::count());
        $this->post('/login', ['login' => 'admin', 'password' => 'admin'])->assertRedirect('/admin/users');
    }
}

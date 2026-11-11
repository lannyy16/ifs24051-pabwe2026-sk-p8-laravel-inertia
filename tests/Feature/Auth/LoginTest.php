<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Auth/Login'));
    }

    public function test_member_can_login_with_username(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia123']);

        $this->post('/login', ['login' => 'budi', 'password' => 'rahasia123', 'remember' => true])
            ->assertRedirect('/dashboard/posts');
        $this->assertAuthenticated();
    }

    public function test_member_can_login_with_email(): void
    {
        User::factory()->create(['email' => 'budi@example.com', 'password' => 'rahasia123']);

        $this->post('/login', ['login' => 'budi@example.com', 'password' => 'rahasia123'])->assertRedirect('/dashboard/posts');
        $this->assertAuthenticated();
    }

    public function test_admin_is_redirected_to_admin_panel(): void
    {
        User::factory()->admin()->create(['username' => 'root', 'password' => 'rahasia123']);

        $this->post('/login', ['login' => 'root', 'password' => 'rahasia123'])->assertRedirect('/admin/users');
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        User::factory()->create(['username' => 'budi', 'password' => 'rahasia123']);

        $this->post('/login', ['login' => 'budi', 'password' => 'salah'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_fields_are_required(): void
    {
        $this->post('/login', [])->assertSessionHasErrors(['login', 'password']);
    }

    public function test_logout_redirects_to_login(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_guests_and_authenticated_users_are_redirected(): void
    {
        $this->get('/dashboard/posts')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/dashboard/posts');
        $this->get('/dashboard')->assertRedirect('/dashboard/posts');
    }
}

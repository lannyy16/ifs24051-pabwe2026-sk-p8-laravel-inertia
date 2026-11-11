<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders(): void
    {
        $this->get('/register')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Auth/Register'));
    }

    public function test_register_creates_member_and_logs_in(): void
    {
        $this->post('/register', [
            'name' => 'Siti', 'username' => 'siti_01', 'email' => 'siti@example.com',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/dashboard/posts');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['username' => 'siti_01', 'email' => 'siti@example.com', 'role' => 'member']);
    }

    public function test_register_validates_input(): void
    {
        User::factory()->create(['username' => 'dipakai', 'email' => 'dipakai@example.com']);

        $this->post('/register', [
            'name' => 'X', 'username' => 'dipakai', 'email' => 'dipakai@example.com',
            'password' => 'abc', 'password_confirmation' => 'xyz',
        ])->assertSessionHasErrors(['username', 'email', 'password']);
    }
}

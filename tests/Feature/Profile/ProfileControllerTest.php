<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_edit_page_shows_user_and_post_count(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertInertia(
            fn (Assert $p) => $p->component('Profile/Edit')->where('user.id', $user->id)->where('postCount', 0)
        );
    }

    public function test_update_identity(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['name' => 'Baru', 'username' => 'baru_1', 'email' => 'baru@example.com'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'username' => 'baru_1', 'email' => 'baru@example.com']);
    }

    public function test_identity_must_be_unique(): void
    {
        User::factory()->create(['username' => 'ada', 'email' => 'ada@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', ['name' => 'N', 'username' => 'ada', 'email' => 'ada@example.com'])
            ->assertSessionHasErrors(['username', 'email']);
    }

    public function test_update_password(): void
    {
        $user = User::factory()->create(['password' => 'lama12345']);

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'lama12345', 'password' => 'baru12345', 'password_confirmation' => 'baru12345',
        ])->assertSessionHas('success');

        $this->assertTrue(Hash::check('baru12345', $user->refresh()->password));
    }

    public function test_update_password_rejects_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => 'lama12345']);

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'salah', 'password' => 'baru12345', 'password_confirmation' => 'baru12345',
        ])->assertSessionHasErrors('current_password');
    }

    public function test_upload_and_delete_photo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/profile/photo', ['photo' => UploadedFile::fake()->image('a.jpg', 600, 300)])
            ->assertSessionHas('success');
        $first = $user->refresh()->profile_photo_path;
        Storage::disk('public')->assertExists($first);

        $this->actingAs($user)->post('/profile/photo', ['photo' => UploadedFile::fake()->image('b.png', 100, 200)]);
        Storage::disk('public')->assertMissing($first);

        $this->actingAs($user)->delete('/profile/photo')->assertSessionHas('success');
        $this->assertNull($user->refresh()->profile_photo_path);
    }

    public function test_photo_must_be_an_image(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/profile/photo', ['photo' => UploadedFile::fake()->create('a.txt', 1, 'text/plain')])
            ->assertSessionHasErrors('photo');
    }
}

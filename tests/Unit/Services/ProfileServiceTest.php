<?php

namespace Tests\Unit\Services;

use App\Features\Profile\Services\ProfileService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProfileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->service = new ProfileService;
    }

    public function test_update_profile_and_password(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($this->service->updateProfile($user, ['name' => 'N', 'username' => 'nn', 'email' => 'n@x.id']));
        $this->assertTrue($this->service->updatePassword($user, 'baru12345'));

        $user->refresh();
        $this->assertSame('nn', $user->username);
        $this->assertTrue(Hash::check('baru12345', $user->password));
    }

    public function test_photo_from_path_is_cropped_to_square(): void
    {
        $user = User::factory()->create();
        $upload = UploadedFile::fake()->image('a.jpg', 800, 400);

        $stored = $this->service->updateProfilePhoto($user, $upload->getRealPath());

        Storage::disk('public')->assertExists($stored);
        $size = getimagesizefromstring(Storage::disk('public')->get($stored));
        $this->assertSame([400, 400], [$size[0], $size[1]]);
        $this->assertSame($stored, $user->refresh()->profile_photo_path);
    }

    public function test_photo_from_base64_and_raw_binary_replaces_old_photo(): void
    {
        $user = User::factory()->create();
        $upload = UploadedFile::fake()->image('a.png', 50, 80);
        $binary = file_get_contents($upload->getRealPath());

        $first = $this->service->updateProfilePhoto($user, 'data:image/png;base64,'.base64_encode($binary));
        $second = $this->service->updateProfilePhoto($user->refresh(), $binary);

        Storage::disk('public')->assertMissing($first);
        Storage::disk('public')->assertExists($second);
    }

    public function test_invalid_image_throws(): void
    {
        $this->expectException(RuntimeException::class);
        $this->service->updateProfilePhoto(User::factory()->create(), 'bukan gambar');
    }

    public function test_delete_photo_with_and_without_existing_photo(): void
    {
        $user = User::factory()->create(['profile_photo_path' => 'profile-photos/x.png']);
        Storage::disk('public')->put('profile-photos/x.png', 'x');

        $this->assertTrue($this->service->deleteProfilePhoto($user));
        Storage::disk('public')->assertMissing('profile-photos/x.png');
        $this->assertNull($user->refresh()->profile_photo_path);

        $this->assertTrue($this->service->deleteProfilePhoto($user));
    }
}

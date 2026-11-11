<?php

namespace Tests\Unit\Services;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_counts_posts_and_searches_by_name_username_email(): void
    {
        $writer = User::factory()->create(['name' => 'Siti Aminah', 'username' => 'siti', 'email' => 'siti@kampus.id']);
        User::factory()->create(['name' => 'Budi', 'username' => 'budi', 'email' => 'budi@x.id']);
        Post::factory()->published()->for($writer)->create();
        Post::factory()->draft()->for($writer)->create();

        $service = new AdminUserService;

        $this->assertSame(2, $service->list(null)->total());
        $this->assertSame(1, $service->list('aminah')->total());
        $this->assertSame(1, $service->list('SITI')->total());
        $this->assertSame(1, $service->list('kampus.id')->total());

        $row = $service->list('siti')->items()[0];
        $this->assertSame(2, $row->posts_count);
        $this->assertSame(1, $row->published_posts_count);
    }
}

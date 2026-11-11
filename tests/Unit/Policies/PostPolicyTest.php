<?php

namespace Tests\Unit\Policies;

use App\Features\Post\Models\Post;
use App\Features\Post\Policies\PostPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_owner_can_manage(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();
        $policy = new PostPolicy;

        $this->assertTrue($policy->manage($owner, $post));
        $this->assertFalse($policy->manage(User::factory()->create(), $post));
        $this->assertFalse($policy->manage(User::factory()->admin()->create(), $post));
    }

    public function test_policy_is_registered_in_gate(): void
    {
        $owner = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        $this->assertTrue(Gate::forUser($owner)->allows('manage', $post));
    }
}

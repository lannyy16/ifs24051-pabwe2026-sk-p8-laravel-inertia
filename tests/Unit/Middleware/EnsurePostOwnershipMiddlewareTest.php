<?php

namespace Tests\Unit\Middleware;

use App\Features\Post\Models\Post;
use App\Http\Middleware\EnsurePostOwnership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsurePostOwnershipMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function invoke(?User $user, string $method = 'GET', array $params = []): int
    {
        $request = Request::create('/x/1', $method);
        $request->setUserResolver(fn () => $user);

        $route = new Route($method, '/x/{id}', []);
        $route->bind($request);
        foreach ($params as $key => $value) {
            $route->setParameter($key, $value);
        }
        $request->setRouteResolver(fn () => $route);

        try {
            return (new EnsurePostOwnership)->handle($request, fn () => response('ok'))->getStatusCode();
        } catch (HttpException $e) {
            return $e->getStatusCode();
        }
    }

    public function test_unauthenticated_request_gets_401(): void
    {
        $this->assertSame(401, $this->invoke(null));
    }

    public function test_request_without_parameters_passes(): void
    {
        $this->assertSame(200, $this->invoke(User::factory()->make()));
    }

    public function test_member_cannot_access_other_user_route(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();

        $this->assertSame(403, $this->invoke($member, 'GET', ['user' => $other]));
        $this->assertSame(403, $this->invoke($member, 'GET', ['user' => $other->id]));
        $this->assertSame(200, $this->invoke($member, 'GET', ['user' => $member]));
    }

    public function test_member_owns_post_checks(): void
    {
        $member = User::factory()->create();
        $mine = Post::factory()->for($member)->create();
        $theirs = Post::factory()->create();

        $this->assertSame(200, $this->invoke($member, 'GET', ['post' => $mine]));
        $this->assertSame(200, $this->invoke($member, 'DELETE', ['post' => $mine->id]));
        $this->assertSame(403, $this->invoke($member, 'GET', ['post' => $theirs]));
        $this->assertSame(200, $this->invoke($member, 'GET', ['post' => 999999]));
    }

    public function test_admin_can_read_but_not_write_other_posts(): void
    {
        $admin = User::factory()->admin()->create();
        $theirs = Post::factory()->create();
        $own = Post::factory()->for($admin)->create();

        $this->assertSame(200, $this->invoke($admin, 'GET', ['post' => $theirs]));
        $this->assertSame(403, $this->invoke($admin, 'PUT', ['post' => $theirs]));
        $this->assertSame(200, $this->invoke($admin, 'PUT', ['post' => $own]));
    }
}

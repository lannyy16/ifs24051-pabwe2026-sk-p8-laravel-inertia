<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnsureRoleMiddlewareTest extends TestCase
{
    private function invoke(?User $user, string $role)
    {
        $request = Request::create('/admin/users');
        $request->setUserResolver(fn () => $user);

        return (new EnsureRole)->handle($request, fn () => response('ok'), $role);
    }

    public function test_allows_matching_role(): void
    {
        $this->assertSame('ok', $this->invoke(User::factory()->admin()->make(), 'admin')->getContent());
    }

    public function test_rejects_other_role_and_guest(): void
    {
        foreach ([User::factory()->make(), null] as $user) {
            try {
                $this->invoke($user, 'admin');
                $this->fail('403 expected');
            } catch (HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }
    }
}

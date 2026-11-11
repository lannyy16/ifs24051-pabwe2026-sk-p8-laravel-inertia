<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Tests\TestCase;

class HandleInertiaRequestsTest extends TestCase
{
    private function request(?User $user): Request
    {
        $request = Request::create('/');
        $request->setUserResolver(fn () => $user);
        $session = new Store('test', new ArraySessionHandler(10));
        $session->put('success', 'Berhasil');
        $request->setLaravelSession($session);

        return $request;
    }

    public function test_share_for_guest(): void
    {
        $shared = (new HandleInertiaRequests)->share($this->request(null));

        $this->assertNull($shared['auth']['user']);
        $this->assertSame(config('app.name'), $shared['appName']);
        $this->assertSame('Berhasil', $shared['flash']['success']());
        $this->assertNull($shared['flash']['error']());
    }

    public function test_share_for_authenticated_user(): void
    {
        $user = User::factory()->make(['id' => 5, 'role' => 'admin']);

        $shared = (new HandleInertiaRequests)->share($this->request($user));

        $this->assertSame(5, $shared['auth']['user']['id']);
        $this->assertSame('admin', $shared['auth']['user']['role']);
        $this->assertArrayHasKey('profile_photo_url', $shared['auth']['user']);
    }

    public function test_version_delegates_to_parent(): void
    {
        $version = (new HandleInertiaRequests)->version(Request::create('/'));
        $this->assertTrue($version === null || is_string($version));
    }
}

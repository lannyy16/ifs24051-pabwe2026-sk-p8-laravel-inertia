<?php

namespace Tests\Unit\Providers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Repositories\PostRepository;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_container_bindings(): void
    {
        $this->assertInstanceOf(PostRepository::class, app(PostRepositoryInterface::class));
        $this->assertInstanceOf(OpenAiPostGeneratorService::class, app(AiClientInterface::class));
    }

    public function test_force_https_config_switches_url_scheme(): void
    {
        config(['app.force_https' => true]);
        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', URL::to('/x'));
    }

    public function test_production_environment_forces_https(): void
    {
        $this->app['env'] = 'production';
        config(['app.force_https' => false]);
        (new AppServiceProvider($this->app))->boot();

        $this->assertStringStartsWith('https://', URL::to('/x'));
    }

    public function test_rate_limiters_allow_1000_per_second_keyed_by_user_or_ip(): void
    {
        $guest = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '10.0.0.1']);
        $authed = Request::create('/');
        $authed->setUserResolver(fn () => User::factory()->make(['id' => 7]));

        foreach (['1000-per-second', 'global', 'api'] as $name) {
            $limiter = RateLimiter::limiter($name);
            $this->assertSame(1000, $limiter($guest)->maxAttempts);
            $this->assertSame('10.0.0.1', $limiter($guest)->key);
            $this->assertSame(7, $limiter($authed)->key);
        }
    }
}

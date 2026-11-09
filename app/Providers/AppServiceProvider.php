<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider; use App\Features\Post\Contracts\PostRepositoryInterface; use App\Features\Post\Repositories\PostRepository; use App\Features\Ai\Contracts\AiClientInterface; use App\Features\Ai\Services\OpenAiPostGeneratorService;
class AppServiceProvider extends ServiceProvider { public function register():void{$this->app->bind(PostRepositoryInterface::class,PostRepository::class);$this->app->bind(AiClientInterface::class,OpenAiPostGeneratorService::class);} public function boot():void{\Illuminate\Support\Facades\Gate::policy(\App\Features\Post\Models\Post::class,\App\Features\Post\Policies\PostPolicy::class);if(config('app.force_https'))\Illuminate\Support\Facades\URL::forceScheme('https');} }

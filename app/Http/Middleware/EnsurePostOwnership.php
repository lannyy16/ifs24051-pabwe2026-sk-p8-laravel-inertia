<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request; use Symfony\Component\HttpFoundation\Response; use App\Features\Post\Models\Post;
class EnsurePostOwnership { public function handle(Request $request,Closure $next):Response{$post=$request->route('post');if($post instanceof Post)abort_unless((int)$post->user_id===(int)$request->user()->getKey(),403);return $next($request);} }

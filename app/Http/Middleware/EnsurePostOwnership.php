<?php

namespace App\Http\Middleware;

use App\Features\Post\Models\Post;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePostOwnership
{
    /**
     * Pastikan member tidak mengakses data milik pengguna lain,
     * dan tidak ada yang (termasuk admin) mengubah artikel milik orang lain.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $current = $request->user();

        if (! $current) {
            abort(401, 'Unauthenticated');
        }

        $routeUser = $request->route('user');
        if ($routeUser) {
            $targetId = $routeUser instanceof User ? $routeUser->id : (int) $routeUser;

            if ($current->isMember() && $current->id !== $targetId) {
                abort(403, 'Akses ditolak: Member tidak dapat mengakses artikel orang lain.');
            }
        }

        $routePost = $request->route('post');
        if ($routePost) {
            $post = $routePost instanceof Post ? $routePost : Post::find($routePost);

            if ($post) {
                if ($current->isMember() && ! $current->can('manage', $post)) {
                    abort(403, 'Akses ditolak: Member tidak dapat mengakses artikel orang lain.');
                }

                if ($request->isMethodSafe() === false && ! $current->can('manage', $post)) {
                    abort(403, 'Akses ditolak: Hanya pemilik artikel yang dapat mengubah artikel ini.');
                }
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Features\Post\Repositories;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PostRepository implements PostRepositoryInterface
{
    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator
    {
        return Post::query()
            ->with('user:id,name,username,profile_photo_path')
            ->where('is_published', true)
            ->when($category, fn (Builder $q) => $q->where('category', $category))
            ->tap(fn (Builder $q) => $this->applySearch($q, $search))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()
            ->with('user:id,name,username,profile_photo_path')
            ->where('is_published', true)
            ->where('slug', $slug)
            ->first();
    }

    public function relatedPublished(Post $post, int $limit = 3): Collection
    {
        return Post::query()
            ->with('user:id,name,username,profile_photo_path')
            ->where('is_published', true)
            ->where('id', '!=', $post->id)
            ->orderByRaw('CASE WHEN category = ? THEN 0 ELSE 1 END', [$post->category])
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function paginateForUser(int $userId, ?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->where('user_id', $userId)
            ->tap(fn (Builder $q) => $this->applySearch($q, $search))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function paginateAll(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Post::query()
            ->with('user:id,name,username')
            ->tap(fn (Builder $q) => $this->applySearch($q, $search))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allForExport(?int $userId = null): Collection
    {
        return Post::query()
            ->with('user:id,name,username')
            ->when($userId, fn (Builder $q) => $q->where('user_id', $userId))
            ->orderBy('id')
            ->get();
    }

    public function stats(int $userId): array
    {
        $total = Post::where('user_id', $userId)->count();
        $published = Post::where('user_id', $userId)->where('is_published', true)->count();

        return ['total' => $total, 'published' => $published, 'draft' => $total - $published];
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Post::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post->refresh();
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    private function applySearch(Builder $query, ?string $search): void
    {
        if ($search === null || trim($search) === '') {
            return;
        }

        $term = '%'.mb_strtolower(trim($search)).'%';

        $query->where(function (Builder $q) use ($term) {
            $q->whereRaw('LOWER(title) LIKE ?', [$term])
                ->orWhereRaw('LOWER(content) LIKE ?', [$term])
                ->orWhereRaw('LOWER(COALESCE(excerpt, \'\')) LIKE ?', [$term]);
        });
    }
}

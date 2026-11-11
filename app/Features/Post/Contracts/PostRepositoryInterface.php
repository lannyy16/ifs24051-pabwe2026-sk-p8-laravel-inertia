<?php

namespace App\Features\Post\Contracts;

use App\Features\Post\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PostRepositoryInterface
{
    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): ?Post;

    /** @return Collection<int, Post> */
    public function relatedPublished(Post $post, int $limit = 3): Collection;

    public function paginateForUser(int $userId, ?string $search, int $perPage = 10): LengthAwarePaginator;

    public function paginateAll(?string $search, int $perPage = 10): LengthAwarePaginator;

    /** @return Collection<int, Post> */
    public function allForExport(?int $userId = null): Collection;

    /** @return array{total: int, published: int, draft: int} */
    public function stats(int $userId): array;

    public function slugExists(string $slug, ?int $ignoreId = null): bool;

    public function create(array $data): Post;

    public function update(Post $post, array $data): Post;

    public function delete(Post $post): void;
}

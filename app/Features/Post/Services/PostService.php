<?php

namespace App\Features\Post\Services;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostService
{
    public function __construct(private readonly PostRepositoryInterface $posts) {}

    public function publicIndex(?string $search, ?string $category): LengthAwarePaginator
    {
        return $this->posts->paginatePublished($search, $category);
    }

    public function publicShow(string $slug): Post
    {
        return $this->posts->findPublishedBySlug($slug)
            ?? throw new NotFoundHttpException('Artikel tidak ditemukan.');
    }

    /** @return Collection<int, Post> */
    public function related(Post $post): Collection
    {
        return $this->posts->relatedPublished($post);
    }

    public function listForUser(User $user, ?string $search): LengthAwarePaginator
    {
        return $this->posts->paginateForUser($user->id, $search);
    }

    public function listAll(?string $search): LengthAwarePaginator
    {
        return $this->posts->paginateAll($search);
    }

    /** @return array{total: int, published: int, draft: int} */
    public function stats(User $user): array
    {
        return $this->posts->stats($user->id);
    }

    public function create(User $user, array $data, ?UploadedFile $cover = null): Post
    {
        $published = (bool) ($data['is_published'] ?? false);

        $payload = [
            'user_id' => $user->id,
            'title' => $data['title'],
            'slug' => $this->makeSlug($data['title']),
            'category' => PostCategory::fromOrDefault($data['category'] ?? null)->value,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'is_published' => $published,
            'published_at' => $published ? now() : null,
        ];

        if ($cover) {
            $payload['cover_image_path'] = $this->storeCover($cover);
        }

        return $this->posts->create($payload);
    }

    public function update(Post $post, array $data, ?UploadedFile $cover = null): Post
    {
        $published = (bool) ($data['is_published'] ?? $post->is_published);

        $payload = [
            'title' => $data['title'],
            'category' => PostCategory::fromOrDefault($data['category'] ?? null)->value,
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'is_published' => $published,
            'published_at' => $published ? ($post->published_at ?? now()) : null,
        ];

        if ($data['title'] !== $post->title) {
            $payload['slug'] = $this->makeSlug($data['title'], $post->id);
        }

        if ($cover) {
            $this->deleteCoverFile($post);
            $payload['cover_image_path'] = $this->storeCover($cover);
        }

        return $this->posts->update($post, $payload);
    }

    public function delete(Post $post): void
    {
        $this->deleteCoverFile($post);
        $this->posts->delete($post);
    }

    public function togglePublish(Post $post): Post
    {
        $publish = ! $post->is_published;

        return $this->posts->update($post, [
            'is_published' => $publish,
            'published_at' => $publish ? now() : null,
        ]);
    }

    public function setCover(Post $post, UploadedFile $cover): Post
    {
        $this->deleteCoverFile($post);

        return $this->posts->update($post, ['cover_image_path' => $this->storeCover($cover)]);
    }

    public function removeCover(Post $post): Post
    {
        $this->deleteCoverFile($post);

        return $this->posts->update($post, ['cover_image_path' => null]);
    }

    /**
     * Buat banyak artikel sekaligus (hasil AI / impor Excel).
     *
     * @param  list<array<string, mixed>>  $items
     */
    public function createMany(User $user, array $items, bool $publish = false): int
    {
        foreach ($items as $item) {
            $this->create($user, [
                'title' => $item['title'],
                'category' => $item['category'] ?? null,
                'excerpt' => $item['excerpt'] ?? null,
                'content' => $item['content'],
                'is_published' => $item['is_published'] ?? $publish,
            ]);
        }

        return count($items);
    }

    /** @return Collection<int, Post> */
    public function forExport(?int $userId = null): Collection
    {
        return $this->posts->allForExport($userId);
    }

    public function makeSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $i = 2;

        while ($this->posts->slugExists($slug, $ignoreId)) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function storeCover(UploadedFile $cover): string
    {
        return $cover->store('covers', 'public');
    }

    private function deleteCoverFile(Post $post): void
    {
        if ($post->cover_image_path) {
            Storage::disk('public')->delete($post->cover_image_path);
        }
    }
}

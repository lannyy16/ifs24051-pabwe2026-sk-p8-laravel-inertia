<?php

namespace App\Features\Admin\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserService
{
    public function list(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        $term = $search !== null ? '%'.mb_strtolower(trim($search)).'%' : null;

        return User::query()
            ->withCount([
                'posts',
                'posts as published_posts_count' => fn ($q) => $q->where('is_published', true),
            ])
            ->when($term, fn ($q) => $q->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(username) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            }))
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}

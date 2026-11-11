<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Services\PostService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController
{
    public function __construct(private readonly PostService $posts) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $category = $request->query('category');

        return Inertia::render('Public/PostIndex', [
            'posts' => $this->posts->publicIndex($search, $category),
            'categories' => PostCategory::options(),
            'filters' => ['search' => $search ?? '', 'category' => $category ?? ''],
        ]);
    }

    public function show(string $slug): Response
    {
        $post = $this->posts->publicShow($slug);

        return Inertia::render('Public/PostShow', [
            'post' => $post,
            'related' => $this->posts->related($post),
        ]);
    }
}

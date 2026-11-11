<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminPostController
{
    public function __construct(
        private readonly PostService $posts,
        private readonly PostExcelService $excel,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');

        return Inertia::render('Admin/PostList', [
            'posts' => $this->posts->listAll($search),
            'filters' => ['search' => $search ?? ''],
        ]);
    }

    public function userPosts(Request $request, User $user): Response
    {
        $search = $request->query('search');

        return Inertia::render('Admin/UserPostList', [
            'author' => $user->only(['id', 'name', 'username']),
            'posts' => $this->posts->listForUser($user, $search),
            'stats' => $this->posts->stats($user),
            'filters' => ['search' => $search ?? ''],
        ]);
    }

    public function export(): BinaryFileResponse
    {
        $path = $this->excel->export($this->posts->forExport(), true);

        return response()->download($path, 'semua-artikel.xlsx')->deleteFileAfterSend(true);
    }

    public function exportUser(User $user): BinaryFileResponse
    {
        $path = $this->excel->export($this->posts->forExport($user->id), true);

        return response()->download($path, "artikel-{$user->username}.xlsx")->deleteFileAfterSend(true);
    }
}

<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Services\AdminUserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController
{
    public function __construct(private readonly AdminUserService $users) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/UserList', [
            'users' => $this->users->list($request->query('search')),
            'filters' => ['search' => $request->query('search', '')],
        ]);
    }
}

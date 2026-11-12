<?php
namespace App\Features\Admin\Services;
use App\Models\User;use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class AdminUserService { public function users():LengthAwarePaginator{return User::withCount('posts')->orderBy('name')->paginate(15);} public function find(int $id):User{return User::with('posts')->findOrFail($id);} }

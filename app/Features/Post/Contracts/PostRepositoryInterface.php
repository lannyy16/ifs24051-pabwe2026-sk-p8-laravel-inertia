<?php
namespace App\Features\Post\Contracts;
use App\Features\Post\Models\Post; use Illuminate\Contracts\Pagination\LengthAwarePaginator;
interface PostRepositoryInterface { public function paginateForUser(int $userId,int $perPage=10):LengthAwarePaginator; public function create(int $userId,array $data):Post; public function update(Post $post,array $data):Post; public function delete(Post $post):void; }

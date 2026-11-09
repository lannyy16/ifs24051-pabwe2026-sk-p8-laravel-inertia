<?php
namespace App\Features\Post\Repositories;
use App\Features\Post\Contracts\PostRepositoryInterface; use App\Features\Post\Models\Post; use Illuminate\Contracts\Pagination\LengthAwarePaginator;
class PostRepository implements PostRepositoryInterface { public function paginateForUser(int $userId,int $perPage=10):LengthAwarePaginator{return Post::where('user_id',$userId)->latest()->paginate($perPage)->withQueryString();} public function create(int $userId,array $data):Post{return Post::create([...$data,'user_id'=>$userId]);} public function update(Post $post,array $data):Post{$post->update($data);return $post->refresh();} public function delete(Post $post):void{$post->delete();} }

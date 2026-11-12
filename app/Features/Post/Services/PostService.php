<?php
namespace App\Features\Post\Services;
use App\Features\Post\Contracts\PostRepositoryInterface; use App\Features\Post\Models\Post; use Illuminate\Support\Facades\DB;
class PostService { public function __construct(private PostRepositoryInterface $posts){} public function create(int $userId,array $data):Post{return DB::transaction(fn()=>$this->posts->create($userId,$data));} public function update(Post $post,array $data):Post{return DB::transaction(fn()=>$this->posts->update($post,$data));} public function delete(Post $post):void{DB::transaction(fn()=>$this->posts->delete($post));} public function togglePublish(Post $post):Post{$post->is_published=!$post->is_published;$post->published_at=$post->is_published?now():null;$post->save();return $post->refresh();} }

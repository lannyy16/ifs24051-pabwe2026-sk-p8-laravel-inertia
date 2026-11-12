<?php
namespace App\Features\Post\Policies;
use App\Features\Post\Models\Post; use App\Models\User;
class PostPolicy { public function view(User $user,Post $post):bool{return $user->isAdmin() || (int)$post->user_id===(int)$user->getKey();} public function update(User $user,Post $post):bool{return (int)$post->user_id===(int)$user->getKey();} public function delete(User $user,Post $post):bool{return (int)$post->user_id===(int)$user->getKey();} }

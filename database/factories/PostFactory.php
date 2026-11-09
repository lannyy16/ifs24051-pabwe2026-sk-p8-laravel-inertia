<?php
namespace Database\Factories;use Illuminate\Database\Eloquent\Factories\Factory;use App\Features\Post\Models\Post;use App\Features\Post\Enums\PostCategory;
class PostFactory extends Factory {protected $model=Post::class;public function definition():array{$published=fake()->boolean();return ['title'=>fake()->sentence(6),'category'=>fake()->randomElement(PostCategory::values()),'excerpt'=>fake()->paragraph(),'content'=>"# ".fake()->sentence()."\n\n".fake()->paragraphs(3,true),'is_published'=>$published,'published_at'=>$published?now():null];}}

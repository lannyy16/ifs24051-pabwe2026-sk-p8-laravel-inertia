<?php

namespace Database\Factories\Features\Post\Models;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $isPublished = fake()->boolean(70);
        $title = fake()->sentence(6);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 999999),
            'category' => fake()->randomElement([
                'Teknologi',
                'Pendidikan',
                'Gaya Hidup',
                'Bisnis',
                'Opini',
                'Umum',
            ]),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'cover_image_path' => null,
            'is_published' => $isPublished,
            'published_at' => $isPublished
                ? fake()->dateTimeBetween('-1 year', 'now')
                : null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
<?php

namespace Database\Seeders;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrator', 'email' => 'admin@example.com', 'password' => 'admin', 'role' => User::ROLE_ADMIN,
        ]);

        $member = User::updateOrCreate(['username' => 'member'], [
            'name' => 'Penulis Member', 'email' => 'member@example.com', 'password' => 'member', 'role' => User::ROLE_MEMBER,
        ]);

        if (! $member->posts()->exists()) {
            Post::factory()->count(4)->published()->for($member)->create();
            Post::factory()->count(2)->draft()->for($member)->create();
        }
    }
}

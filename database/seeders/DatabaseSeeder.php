<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Alessandro Bonatti
        User::factory()->create([
            'name' => 'Alessandro Bonatti',
            'email' => 'alessandro.bonatti@elva11.se',
            'personnummer' => '19760117-6998',
            'password' => bcrypt('password'),
        ]);

        // Create two additional random users
        User::factory()->count(2)->create([
            'password' => bcrypt('password'),
        ]);
    }
}

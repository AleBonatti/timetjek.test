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

        // User 2 - Secondary test user
        User::create([
            'personnummer' => '19900101-1234',
            'name' => 'Anna Svensson',
            'email' => 'anna.svensson@example.com',
            'password' => bcrypt('password'),
        ]);

        // User 3 - Third test user
        User::create([
            'personnummer' => '19850523-5678',
            'name' => 'Lars Johansson',
            'email' => 'lars.johansson@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}

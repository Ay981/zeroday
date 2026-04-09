<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create your specific Test User with 50 reports
        User::factory()->hasReports(50)->create([
            'name' => 'Cyber Researcher',
            'email' => 'hacker@zeroday.com',
            'password' => bcrypt('password123'), // Good to set a password you know
        ]);

        // 2. Create 10 more random users, each with 50 reports (500 total)
        User::factory(10)->hasReports(50)->create();
    }
}

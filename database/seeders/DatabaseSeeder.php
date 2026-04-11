<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create 3 Programs

        $tesla = Program::create([
            'name' => 'Tesla Security',
            'slug' => 'tesla-security',
            'description' => 'Help us secure our fleet of autonomous vehicles.',
            'bounty_multiplier' => 2.50, // High reward
        ]);

        $nasa = Program::create([
            'name' => 'NASA JPL',
            'slug' => 'nasa-jpl',
            'description' => 'Security for the stars.',
            'bounty_multiplier' => 5.00, // Elite reward
        ]);
        $google = Program::create([
            'name' => 'Google',
            'slug' => 'google',
            'description' => 'Secure our search engine and cloud services.',
            'bounty_multiplier' => 1.50, // Standard reward
        ]);

        // Update your Report Factory logic to pick a random program_id
        User::factory(10)->hasReports(20, [
            'program_id' => $tesla->id,
        ])->create();
    }
}

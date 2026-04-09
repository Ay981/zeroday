<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class ReportsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'severity' => fake()->randomElement(['Low', 'Medium', 'High', 'Critical']),
            'description' => fake()->paragraph(3),
            'status' => 'Open',
            
        ];
    }
}

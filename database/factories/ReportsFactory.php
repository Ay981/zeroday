<?php

namespace Database\Factories;

use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportsFactory extends Factory
{
    protected $model = Report::class;

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

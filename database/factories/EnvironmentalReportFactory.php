<?php

namespace Database\Factories;

use App\Models\EnvironmentalReport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnvironmentalReport>
 */
class EnvironmentalReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_number' => 'ENV-'.fake()->unique()->numerify('######'),
            'submitted_at' => now(),
            'user_id' => User::factory(),
            'category' => fake()->randomElement([
                'illegal_dumping',
                'damaged_tree',
                'air_pollution',
                'water_pollution',
                'green_space',
                'other',
            ]),
            'description' => fake()->sentence(),
            'additional_information' => fake()->optional()->sentence(),
            'address' => fake()->streetAddress(),
            'latitude' => 44.8528,
            'longitude' => 65.5092,
            'priority' => 'medium',
            'status' => 'new',
            'assigned_to' => null,
        ];
    }
}

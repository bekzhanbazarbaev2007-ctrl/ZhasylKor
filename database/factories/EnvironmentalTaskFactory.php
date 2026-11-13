<?php

namespace Database\Factories;

use App\Models\EnvironmentalReport;
use App\Models\EnvironmentalTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EnvironmentalTask>
 */
class EnvironmentalTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'task_number' => 'TSK-'.fake()->unique()->numerify('######'),
            'report_id' => EnvironmentalReport::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->sentence(),
            'category' => 'Waste removal',
            'address' => fake()->streetAddress(),
            'latitude' => 44.8528,
            'longitude' => 65.5092,
            'assigned_to' => User::factory(),
            'created_by' => User::factory(),
            'priority' => 'medium',
            'status' => 'assigned',
            'due_date' => now()->addDay(),
            'completion_photo_path' => null,
            'completion_notes' => null,
            'completed_at' => null,
        ];
    }
}

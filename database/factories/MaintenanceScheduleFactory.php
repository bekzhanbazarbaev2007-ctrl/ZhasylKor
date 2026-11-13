<?php

namespace Database\Factories;

use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceSchedule>
 */
class MaintenanceScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'MNT-'.fake()->unique()->numerify('######'),
            'title' => fake()->sentence(4),
            'maintenance_type' => 'watering',
            'description' => null,
            'location_label' => fake()->streetAddress(),
            'green_asset_id' => null,
            'task_id' => null,
            'assigned_to' => User::factory(),
            'scheduled_at' => now()->addDay(),
            'status' => 'scheduled',
            'completed_at' => null,
        ];
    }
}

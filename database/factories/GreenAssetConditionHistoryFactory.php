<?php

namespace Database\Factories;

use App\Models\GreenAsset;
use App\Models\GreenAssetConditionHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GreenAssetConditionHistory>
 */
class GreenAssetConditionHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'green_asset_id' => GreenAsset::factory(),
            'condition' => fake()->randomElement(['healthy', 'needs_watering', 'diseased', 'drying', 'pest_damage']),
            'notes' => fake()->optional()->sentence(),
            'updated_by' => User::factory(),
        ];
    }
}

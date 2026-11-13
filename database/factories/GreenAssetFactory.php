<?php

namespace Database\Factories;

use App\Models\GreenAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GreenAsset>
 */
class GreenAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_number' => 'TREE-'.fake()->unique()->numerify('#####'),
            'name' => fake()->randomElement(['Siberian elm', 'Eastern poplar', 'Ash tree']),
            'type' => 'tree',
            'condition' => 'healthy',
            'address' => fake()->streetAddress(),
            'latitude' => 44.8528,
            'longitude' => 65.5092,
            'photos' => null,
            'registration_date' => now()->toDateString(),
        ];
    }
}

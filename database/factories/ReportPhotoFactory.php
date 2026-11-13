<?php

namespace Database\Factories;

use App\Models\EnvironmentalReport;
use App\Models\ReportPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportPhoto>
 */
class ReportPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'report_id' => EnvironmentalReport::factory(),
            'file_path' => 'reports/'.fake()->uuid().'.jpg',
            'photo_type' => 'before',
            'uploaded_by' => User::factory(),
        ];
    }
}

<?php

namespace App\Models;

use Database\Factories\GreenAssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'asset_number', 'name', 'type', 'species', 'description', 'condition',
    'address', 'latitude', 'longitude', 'photos', 'registration_date',
])]
class GreenAsset extends Model
{
    /** @use HasFactory<GreenAssetFactory> */
    use HasFactory;

    protected $table = 'green_assets';

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'photos' => 'array',
            'registration_date' => 'date',
        ];
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function conditionHistory(): HasMany
    {
        return $this->hasMany(GreenAssetConditionHistory::class)->latest();
    }

    public function getRouteKeyName(): string
    {
        return 'asset_number';
    }
}

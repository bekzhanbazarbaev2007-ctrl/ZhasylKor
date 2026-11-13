<?php

namespace App\Models;

use Database\Factories\MaintenanceScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'code', 'title', 'maintenance_type', 'description', 'location_label',
    'green_asset_id', 'task_id', 'assigned_to', 'scheduled_at', 'completed_at', 'status',
])]
class MaintenanceSchedule extends Model
{
    /** @use HasFactory<MaintenanceScheduleFactory> */
    use HasFactory;

    protected $table = 'maintenance_records';

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function greenAsset(): BelongsTo
    {
        return $this->belongsTo(GreenAsset::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(EnvironmentalTask::class, 'task_id');
    }
}

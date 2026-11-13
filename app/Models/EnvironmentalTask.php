<?php

namespace App\Models;

use Database\Factories\EnvironmentalTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'task_number', 'report_id', 'title', 'description', 'category', 'address',
    'latitude', 'longitude', 'assigned_to', 'created_by', 'priority', 'status',
    'due_date', 'completion_photo_path', 'completion_notes', 'completed_at',
])]
class EnvironmentalTask extends Model
{
    /** @use HasFactory<EnvironmentalTaskFactory> */
    use HasFactory;

    protected $table = 'tasks';

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(EnvironmentalReport::class, 'report_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'task_number';
    }
}

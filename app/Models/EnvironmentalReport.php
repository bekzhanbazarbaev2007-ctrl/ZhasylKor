<?php

namespace App\Models;

use Database\Factories\EnvironmentalReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'report_number', 'user_id', 'category', 'description', 'additional_information',
    'address', 'latitude', 'longitude', 'priority', 'status', 'assigned_to', 'submitted_at',
])]
class EnvironmentalReport extends Model
{
    /** @use HasFactory<EnvironmentalReportFactory> */
    use HasFactory;

    protected $table = 'reports';

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ReportPhoto::class, 'report_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EnvironmentalTask::class, 'report_id');
    }

    public function getRouteKeyName(): string
    {
        return 'report_number';
    }
}

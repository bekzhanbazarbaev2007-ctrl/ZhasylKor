<?php

namespace App\Models;

use Database\Factories\ReportPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['report_id', 'file_path', 'photo_type', 'uploaded_by'])]
class ReportPhoto extends Model
{
    /** @use HasFactory<ReportPhotoFactory> */
    use HasFactory;

    public function report(): BelongsTo
    {
        return $this->belongsTo(EnvironmentalReport::class, 'report_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

<?php

namespace App\Models;

use Database\Factories\GreenAssetConditionHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['green_asset_id', 'condition', 'notes', 'updated_by'])]
class GreenAssetConditionHistory extends Model
{
    /** @use HasFactory<GreenAssetConditionHistoryFactory> */
    use HasFactory;

    public function greenAsset(): BelongsTo
    {
        return $this->belongsTo(GreenAsset::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

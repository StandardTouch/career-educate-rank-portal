<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalysisImport extends Model
{
    protected $guarded = [];

    public function analysisDataset(): BelongsTo
    {
        return $this->belongsTo(AnalysisDataset::class);
    }

    public function sheets(): HasMany
    {
        return $this->hasMany(AnalysisImportSheet::class);
    }
}

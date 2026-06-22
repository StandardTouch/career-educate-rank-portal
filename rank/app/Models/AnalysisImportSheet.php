<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisImportSheet extends Model
{
    protected $guarded = [];

    public function analysisImport(): BelongsTo
    {
        return $this->belongsTo(AnalysisImport::class);
    }

    public function analysisRound(): BelongsTo
    {
        return $this->belongsTo(AnalysisRound::class);
    }
}

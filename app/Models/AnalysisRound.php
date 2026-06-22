<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisRound extends Model
{
    protected $guarded = [];

    public function analysisDataset(): BelongsTo
    {
        return $this->belongsTo(AnalysisDataset::class);
    }
}

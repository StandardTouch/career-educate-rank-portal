<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisRecord extends Model
{
    protected $guarded = [];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function analysisDataset(): BelongsTo
    {
        return $this->belongsTo(AnalysisDataset::class);
    }

    public function analysisRound(): BelongsTo
    {
        return $this->belongsTo(AnalysisRound::class);
    }

    public function analysisImport(): BelongsTo
    {
        return $this->belongsTo(AnalysisImport::class);
    }

    public function analysisImportSheet(): BelongsTo
    {
        return $this->belongsTo(AnalysisImportSheet::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalysisDataset extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function rounds(): HasMany
    {
        return $this->hasMany(AnalysisRound::class);
    }

    public function analysisRecords(): HasMany
    {
        return $this->hasMany(AnalysisRecord::class);
    }

    public function imports(): HasMany
    {
        return $this->hasMany(AnalysisImport::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportSheet extends Model
{
    protected $fillable = [
        'import_id',
        'sheet_name',
        'sheet_type',
        'round_id',
        'row_count',
    ];

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function rankRecords(): HasMany
    {
        return $this->hasMany(RankRecord::class);
    }
}

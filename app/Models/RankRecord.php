<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RankRecord extends Model
{
    protected $fillable = [
        'dataset_id',
        'import_id',
        'import_sheet_id',
        'round_id',
        'college_name',
        'course',
        'quota',
        'category',
        'local_area',
        'seats',
        'opening_rank',
        'closing_rank',
        'marks',
        'fem_closing_rank',
        'fem_closing_mark',
        'fees',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'fees' => 'decimal:2',
            'marks' => 'decimal:2',
            'fem_closing_mark' => 'decimal:2',
        ];
    }

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }
}

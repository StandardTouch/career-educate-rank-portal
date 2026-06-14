<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Import extends Model
{
    protected $fillable = [
        'dataset_id',
        'original_filename',
        'stored_path',
        'status',
        'imported_by',
        'total_rows',
        'error_message',
    ];

    public function dataset(): BelongsTo
    {
        return $this->belongsTo(Dataset::class);
    }

    public function sheets(): HasMany
    {
        return $this->hasMany(ImportSheet::class);
    }
}

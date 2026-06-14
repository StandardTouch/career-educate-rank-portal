<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dataset extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'year',
        'course',
        'state',
        'quota',
        'descriptor',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'year' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imports(): HasMany
    {
        return $this->hasMany(Import::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class);
    }

    public function rankRecords(): HasMany
    {
        return $this->hasMany(RankRecord::class);
    }
}

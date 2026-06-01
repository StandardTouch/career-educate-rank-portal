<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Round extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    /**
     * Get the NEET cutoff records for this round.
     *
     * @return HasMany<AllIndiaRounds2025>
     */
    public function cutoffs(): HasMany
    {
        return $this->hasMany(AllIndiaRounds2025::class, 'round_id');
    }
}

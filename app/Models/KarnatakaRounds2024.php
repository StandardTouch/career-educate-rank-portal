<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KarnatakaRounds2024 extends Model
{
   protected $table = 'karnataka_2024_rounds';

    protected $fillable = [
        'round_id',
        'college_name',
        'category',
        'local_area',
        'total_seats',
        'gen_closing_rank',
        'fem_closing_rank',
        'gen_closing_mark',
        'fem_closing_mark',
        'tuition_fee',
    ];

    /**
     * Get the round that this cutoff record belongs to.
     *
     * @return BelongsTo<Round, KarnatakaRounds2024>
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'round_id');
    }
}

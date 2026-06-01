<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AllIndiaRounds2025 extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'all_india_rounds_2025';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'round_id',
        'state_name',
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
     * @return BelongsTo<Round, AllIndiaRounds2025>
     */
    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class, 'round_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karnataka2025 extends Model
{
    protected $table = 'karnataka_2025';

    protected $fillable = [
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
}

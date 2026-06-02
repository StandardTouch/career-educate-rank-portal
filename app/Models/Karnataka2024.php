<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karnataka2024 extends Model
{
        protected $table = 'karnataka_2024';

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

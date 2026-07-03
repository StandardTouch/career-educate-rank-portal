<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseMapping extends Model
{
    protected $fillable = [
        'alias',
        'course',
        'source',
    ];
}

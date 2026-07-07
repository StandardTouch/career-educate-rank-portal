<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDocument extends Model
{
    protected $fillable = [
        'title',
        'original_filename',
        'stored_path',
        'dropdown_name',
        'menu_folder_id',
        'is_active',
        'sort_order',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function menuFolder(): BelongsTo
    {
        return $this->belongsTo(MenuFolder::class);
    }
}

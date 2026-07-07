<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MenuFolder extends Model
{
    public const MAX_DEPTH = 3;

    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'depth',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'depth' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function activeChildren(): HasMany
    {
        return $this->children()
            ->where('is_active', true)
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    public function notificationDocuments(): HasMany
    {
        return $this->hasMany(NotificationDocument::class);
    }

    public function activeNotificationDocuments(): HasMany
    {
        return $this->notificationDocuments()
            ->where('is_active', true)
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->latest('id');
    }

    public function canHaveChildren(): bool
    {
        return $this->depth < self::MAX_DEPTH;
    }

    public function pathTitle(): string
    {
        $titles = collect([$this->title]);
        $parent = $this->parent;

        while ($parent) {
            $titles->prepend($parent->title);
            $parent = $parent->parent;
        }

        return $titles->implode(' / ');
    }

    public static function makeSlug(string $title): string
    {
        return Str::slug($title) ?: Str::random(8);
    }
}

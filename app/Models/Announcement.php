<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category',
        'is_urgent',
        'is_pinned',
        'is_published',
        'cover_image',
        'published_at',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'is_urgent' => 'boolean',
            'is_pinned' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(AnnouncementComment::class)->latest();
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function isReadBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->reads()->where('user_id', $user->id)->exists();
    }
}

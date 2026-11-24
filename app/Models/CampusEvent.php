<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampusEvent extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'venue_name',
        'starts_at',
        'ends_at',
        'image',
        'is_featured',
        'is_published',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function userRsvpStatus(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return $this->rsvps()->where('user_id', $user->id)->value('status');
    }
}

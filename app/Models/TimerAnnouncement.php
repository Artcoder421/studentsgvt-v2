<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimerAnnouncement extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'category',
        'starts_at',
        'ends_at',
        'cta_label',
        'cta_url',
        'target_amount',
        'raised_amount',
        'is_active',
        'priority',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeLive(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where('ends_at', '>', $now)
            ->orderByDesc('priority')
            ->orderBy('ends_at');
    }

    public function progressPercent(): ?int
    {
        if (! $this->target_amount || $this->target_amount <= 0) {
            return null;
        }

        return min(100, (int) round(($this->raised_amount / $this->target_amount) * 100));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CharityCampaign extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'beneficiary',
        'description',
        'target_amount',
        'raised_amount',
        'ends_at',
        'payment_instructions',
        'image',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'ends_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(CharityContribution::class)->latest();
    }

    public function progressPercent(): int
    {
        if ($this->target_amount <= 0) {
            return 0;
        }

        return min(100, (int) round(($this->raised_amount / $this->target_amount) * 100));
    }
}

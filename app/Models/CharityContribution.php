<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharityContribution extends Model
{
    protected $fillable = [
        'charity_campaign_id',
        'user_id',
        'donor_name',
        'amount',
        'payment_reference',
        'message',
        'is_anonymous',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CharityCampaign::class, 'charity_campaign_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

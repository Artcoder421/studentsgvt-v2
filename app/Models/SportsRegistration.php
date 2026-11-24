<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SportsRegistration extends Model
{
    protected $fillable = ['sports_activity_id', 'user_id', 'position_or_skill', 'status'];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(SportsActivity::class, 'sports_activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

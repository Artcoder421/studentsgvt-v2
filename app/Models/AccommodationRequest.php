<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccommodationRequest extends Model
{
    protected $fillable = [
        'accommodation_id',
        'user_id',
        'preferred_room_type',
        'move_in_semester',
        'notes',
        'status',
        'admin_response',
    ];

    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

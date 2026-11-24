<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueBooking extends Model
{
    protected $fillable = [
        'venue_id',
        'user_id',
        'event_title',
        'organization_or_course',
        'booking_date',
        'start_time',
        'end_time',
        'expected_attendees',
        'purpose',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

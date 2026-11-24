<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $fillable = [
        'name',
        'code',
        'building',
        'capacity',
        'category',
        'amenities',
        'description',
        'image',
        'status',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(VenueBooking::class);
    }

    public function amenitiesList(): array
    {
        if (! $this->amenities) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->amenities))));
    }
}

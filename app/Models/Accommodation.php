<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accommodation extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'gender_policy',
        'location',
        'distance_from_campus',
        'price_per_semester',
        'available_rooms',
        'total_rooms',
        'amenities',
        'contact_person',
        'contact_phone',
        'description',
        'image',
        'is_verified',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(AccommodationRequest::class);
    }

    public function amenitiesList(): array
    {
        if (! $this->amenities) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $this->amenities))));
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'on_campus_hostel' => 'On-Campus Hostel',
            'verified_off_campus' => 'Verified Off-Campus',
            'shared_apartment' => 'Student Apartment',
            default => ucfirst($this->type),
        };
    }
}

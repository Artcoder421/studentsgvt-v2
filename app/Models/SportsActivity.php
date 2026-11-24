<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SportsActivity extends Model
{
    protected $fillable = [
        'name',
        'sport_type',
        'captain_name',
        'schedule',
        'venue',
        'description',
        'upcoming_match',
        'match_date',
        'last_result',
        'is_open_for_joining',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'is_open_for_joining' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(SportsRegistration::class);
    }
}

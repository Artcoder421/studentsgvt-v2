<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    protected $fillable = [
        'code',
        'name',
        'level',
        'faculty',
        'duration_years',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class);
    }

    public function defaultCommunity(): HasOne
    {
        return $this->hasOne(Community::class)->where('type', 'course_default');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function levelLabel(): string
    {
        return match ($this->level) {
            'bachelor' => "Bachelor's Degree",
            'diploma' => 'Ordinary Diploma',
            'certificate' => 'Technician Certificate',
            'masters' => "Master's Degree",
            default => ucfirst($this->level),
        };
    }
}

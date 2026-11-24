<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    public const POSITIONS = [
        'President',
        'Vice President',
        'Parliament Representative',
        'General Secretary',
        'Treasurer',
    ];

    protected $fillable = [
        'election_id',
        'user_id',
        'fullname',
        'reg_no',
        'course_id',
        'academic_level',
        'position',
        'motto',
        'manifesto',
        'photo',
        'status',
    ];

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}

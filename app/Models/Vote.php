<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'election_id',
        'candidate_id',
        'voter_id',
        'position',
        'course_id',
        'voted_at',
    ];

    protected function casts(): array
    {
        return [
            'voted_at' => 'datetime',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Election extends Model
{
    protected $fillable = [
        'title',
        'academic_year',
        'description',
        'is_enabled',
        'applications_open',
        'voting_open',
        'results_published',
        'voting_starts_at',
        'voting_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'applications_open' => 'boolean',
            'voting_open' => 'boolean',
            'results_published' => 'boolean',
            'voting_starts_at' => 'datetime',
            'voting_ends_at' => 'datetime',
        ];
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function approvedCandidates(): HasMany
    {
        return $this->hasMany(Candidate::class)->where('status', 'approved');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function isVotingActive(): bool
    {
        if (! $this->is_enabled || ! $this->voting_open) {
            return false;
        }

        $now = now();
        if ($this->voting_starts_at && $now->lt($this->voting_starts_at)) {
            return false;
        }

        if ($this->voting_ends_at && $now->gt($this->voting_ends_at)) {
            return false;
        }

        return true;
    }

    public function hasUserVotedForPosition(User $user, string $position): bool
    {
        return $this->votes()
            ->where('voter_id', $user->id)
            ->where('position', $position)
            ->exists();
    }
}

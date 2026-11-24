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
        'contact_email',
        'contact_phone',
        'course_id',
        'academic_level',
        'position',
        'motto',
        'manifesto',
        'photo',
        'application_fee_amount',
        'payment_reference',
        'payment_status',
        'interview_status',
        'interview_date',
        'interview_notes',
        'status',
    ];

    public static function feeForPosition(string $position): int
    {
        return match ($position) {
            'President' => 25000,
            'Vice President' => 20000,
            'General Secretary', 'Treasurer' => 15000,
            default => 10000, // Parliament Representative
        };
    }

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

    public function stageLabel(): string
    {
        if ($this->interview_status === 'failed' || $this->status === 'declined') {
            return 'Did Not Pass Interview';
        }
        if ($this->status === 'approved' || $this->interview_status === 'passed') {
            return 'Passed Interview — Approved on Ballot';
        }
        if ($this->payment_status === 'unpaid') {
            return 'Awaiting Application Fee Payment';
        }
        if ($this->payment_status === 'submitted') {
            return 'Payment Submitted — Pending Verification';
        }
        if ($this->interview_status === 'scheduled') {
            return 'Fee Verified — Interview Scheduled';
        }

        return 'Under Electoral Commission Review';
    }
}

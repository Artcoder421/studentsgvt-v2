<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpTicket extends Model
{
    public const CATEGORIES = [
        'academic' => 'Academic & SIMS Portal',
        'finance_heslb' => 'Tuition, Fees & HESLB Loans',
        'accommodation' => 'Hostels & Accommodation',
        'welfare' => 'Student Welfare & Counseling',
        'it_support' => 'IT & Campus Wi-Fi',
        'grievance' => 'Confidential Grievance',
        'other' => 'General Inquiry',
    ];

    protected $fillable = [
        'ticket_number',
        'user_id',
        'category',
        'subject',
        'description',
        'priority',
        'status',
        'is_anonymous',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(HelpTicketReply::class)->oldest();
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }
}

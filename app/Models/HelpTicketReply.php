<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpTicketReply extends Model
{
    protected $fillable = ['help_ticket_id', 'user_id', 'message', 'is_staff_reply'];

    protected function casts(): array
    {
        return [
            'is_staff_reply' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(HelpTicket::class, 'help_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

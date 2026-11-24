<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostFoundItem extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'item_name',
        'category',
        'description',
        'location',
        'date_occurred',
        'contact_info',
        'image',
        'status',
        'claimed_by_info',
    ];

    protected function casts(): array
    {
        return [
            'date_occurred' => 'date',
        ];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

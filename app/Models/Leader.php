<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leader extends Model
{
    protected $fillable = [
        'name',
        'position',
        'category',
        'department',
        'bio',
        'quote',
        'photo',
        'email',
        'phone',
        'office_location',
        'sort_order',
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}

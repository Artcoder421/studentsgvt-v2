<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'course_id',
        'cr_user_id',
        'created_by',
        'is_official',
        'is_locked',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_official' => 'boolean',
            'is_locked' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function cr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cr_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'community_members')
            ->withPivot(['member_role', 'joined_at']);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(CommunityMessage::class);
    }

    public function hasMember(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->members()->where('users.id', $user->id)->exists();
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'course_default' => 'Official Course Channel',
            'course_group' => 'Course Sub-Community',
            'general' => 'General Campus Channel',
            default => ucfirst($this->type),
        };
    }
}

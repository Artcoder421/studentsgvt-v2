<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CLASS_REP = 'class_rep';
    public const ROLE_STUDENT = 'student';

    public const ROLES = [
        self::ROLE_SUPER_ADMIN => 'Super Admin',
        self::ROLE_ADMIN => 'SONIT Admin',
        self::ROLE_CLASS_REP => 'Class Representative (CR)',
        self::ROLE_STUDENT => 'Student',
    ];

    protected $fillable = [
        'name',
        'email',
        'reg_no',
        'password',
        'role',
        'course_id',
        'year_joined',
        'academic_level',
        'phone',
        'avatar',
        'bio',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function communities(): BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'community_members')
            ->withPivot(['member_role', 'joined_at']);
    }

    public function ledCommunities(): HasMany
    {
        return $this->hasMany(Community::class, 'cr_user_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'voter_id');
    }

    public function helpTickets(): HasMany
    {
        return $this->hasMany(HelpTicket::class);
    }

    public function accommodationRequests(): HasMany
    {
        return $this->hasMany(AccommodationRequest::class);
    }

    public function venueBookings(): HasMany
    {
        return $this->hasMany(VenueBooking::class);
    }

    public function announcementReads(): HasMany
    {
        return $this->hasMany(AnnouncementRead::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN], true);
    }

    public function isClassRep(): bool
    {
        return $this->role === self::ROLE_CLASS_REP;
    }

    public function canCreateCommunityForCourse(?int $courseId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isClassRep()
            && $courseId !== null
            && $this->course_id !== null
            && (int) $this->course_id === (int) $courseId;
    }

    public function canModerateCommunity(Community $community): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($this->isClassRep()) {
            if ($community->cr_user_id && (int) $community->cr_user_id === (int) $this->id) {
                return true;
            }

            return $community->course_id !== null
                && $this->course_id !== null
                && (int) $community->course_id === (int) $this->course_id;
        }

        return false;
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    public function unreadAnnouncementsCount(): int
    {
        $readIds = $this->announcementReads()->pluck('announcement_id');

        return Announcement::query()
            ->where('is_published', true)
            ->when($readIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $readIds))
            ->count();
    }

    public function unreadUrgentCount(): int
    {
        $readIds = $this->announcementReads()->pluck('announcement_id');

        return Announcement::query()
            ->where('is_published', true)
            ->where('is_urgent', true)
            ->when($readIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $readIds))
            ->count();
    }
}

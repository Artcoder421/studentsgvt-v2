<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'module',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $module, string $action, string $description, ?User $actor = null): void
    {
        try {
            $user = $actor ?? auth()->user();
            $req = request();

            static::create([
                'user_id' => $user?->id,
                'user_name' => $user?->name ?? 'Guest / System',
                'user_role' => $user?->role ?? 'guest',
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'ip_address' => $req?->ip(),
                'user_agent' => $req ? substr((string) $req->userAgent(), 0, 250) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Never disrupt user workflow if logging table isn't ready
        }
    }
}

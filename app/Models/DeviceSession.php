<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DeviceSession extends Model
{
    protected $fillable = [
        'member_id',
        'hashed_token',
        'ip_address',
        'user_agent',
        'last_seen',
        'expires_at',
        'revoked',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'expires_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function isActive(): bool
    {
        return ! $this->revoked && (! $this->expires_at || $this->expires_at->isFuture());
    }
}

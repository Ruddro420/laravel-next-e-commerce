<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Coupon status: Active / Scheduled / Expired / Inactive
     * Uses a small grace window so "current time" doesn't become Scheduled.
     */
    public function statusLabel(int $graceSeconds = 120): string
    {
        $now = Carbon::now(); // uses app timezone
        $startsAt = $this->starts_at ? Carbon::parse($this->starts_at) : null;
        $expiresAt = $this->expires_at ? Carbon::parse($this->expires_at) : null;

        if ($expiresAt && $now->gt($expiresAt)) return 'Expired';
        if ($this->is_active === false) return 'Inactive';

        // ✅ grace window prevents "Scheduled" for current minute
        if ($startsAt && $startsAt->gt($now->copy()->addSeconds($graceSeconds))) {
            return 'Scheduled';
        }

        return 'Active';
    }

    // Optional helpers if you want:
    public function isActiveNow(int $graceSeconds = 120): bool
    {
        return $this->statusLabel($graceSeconds) === 'Active';
    }
}
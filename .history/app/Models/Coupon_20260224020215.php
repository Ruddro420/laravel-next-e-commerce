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
        'value' => 'float',
        'min_order_amount' => 'float',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
    ];

    /**
     * Get coupon status label.
     * Returns: Active | Scheduled | Expired | Inactive
     */
    public function statusLabel(int $graceSeconds = 60): string
    {
        $now = Carbon::now();

        // Expired
        if ($this->expires_at && $now->gt($this->expires_at)) {
            return 'Expired';
        }

        // Inactive (manually disabled)
        if ($this->is_active === false) {
            return 'Inactive';
        }

        // Scheduled (starts in future)
        // Grace seconds prevents "current time" showing Scheduled
        if ($this->starts_at && $now->lt($this->starts_at->copy()->subSeconds($graceSeconds))) {
            return 'Scheduled';
        }

        return 'Active';
    }

    /**
     * Check if coupon is currently active
     */
    public function isActiveNow(): bool
    {
        return $this->statusLabel() === 'Active';
    }

    /**
     * Check if coupon is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    /**
     * Check if coupon is scheduled
     */
    public function isScheduled(): bool
    {
        return $this->starts_at && now()->lt($this->starts_at);
    }
}
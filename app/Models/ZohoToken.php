<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ZohoToken extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'token_type',
        'expires_in',
        'expires_at',
        'organization_id',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return true;
        }
        
        // Consider expired 5 minutes before actual expiry
        return Carbon::now()->addMinutes(5)->isAfter($this->expires_at);
    }

    /**
     * Get active token
     */
    public static function getActive()
    {
        return self::where('is_active', true)->latest()->first();
    }
}
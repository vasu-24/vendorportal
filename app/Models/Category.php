<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'hsn_sac_code',
        'status',
        'zoho_account_id',      // 👈 NEW
        'zoho_account_name',    // 👈 NEW
    ];

    // =====================================================
    // CONSTANTS
    // =====================================================

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to get only inactive categories
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope to get categories with Zoho mapping
     */
    public function scopeZohoMapped($query)
    {
        return $query->whereNotNull('zoho_account_id');
    }

    /**
     * Scope to get categories without Zoho mapping
     */
    public function scopeZohoUnmapped($query)
    {
        return $query->whereNull('zoho_account_id');
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get contract items using this category
     */
    public function contractItems()
    {
        return $this->hasMany(ContractItem::class);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if category is active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if category is mapped to Zoho
     */
    public function isZohoMapped()
    {
        return !empty($this->zoho_account_id);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        if ($this->status === self::STATUS_ACTIVE) {
            return '<span class="badge bg-success">Active</span>';
        }
        return '<span class="badge bg-secondary">Inactive</span>';
    }

    /**
     * Get Zoho mapping badge HTML
     */
    public function getZohoBadgeAttribute()
    {
        if ($this->isZohoMapped()) {
            return '<span class="badge bg-info">Mapped</span>';
        }
        return '<span class="badge bg-warning">Not Mapped</span>';
    }
}
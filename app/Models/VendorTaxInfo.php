<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class VendorTaxInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_tax_infos';

    protected $fillable = [
        'vendor_id',
        'tax_residency',
        'gst_reverse_charge',
        'sez_status',
        'tds_exemption_path',
        'tds_section',
        'tds_rate',
        'tds_valid_from',
        'tds_valid_to',
    ];

    protected $casts = [
        'tds_rate' => 'decimal:2',
        'tds_valid_from' => 'date',
        'tds_valid_to' => 'date',
    ];

    /**
     * Relationship: Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get TDS exemption certificate URL
     */
    public function getTdsExemptionUrlAttribute()
    {
        return $this->tds_exemption_path 
            ? Storage::disk('public')->url($this->tds_exemption_path) 
            : null;
    }

    /**
     * Check if TDS exemption is valid
     */
    public function isTdsExemptionValid()
    {
        if (!$this->tds_valid_to) return false;
        return $this->tds_valid_to->isFuture();
    }

    /**
     * Check if SEZ vendor
     */
    public function isSezVendor()
    {
        return $this->sez_status === 'Yes';
    }

    /**
     * Check if reverse charge applicable
     */
    public function isReverseChargeApplicable()
    {
        return $this->gst_reverse_charge === 'Yes';
    }

    /**
     * Scope: SEZ vendors
     */
    public function scopeSez($query)
    {
        return $query->where('sez_status', 'Yes');
    }

    /**
     * Scope: Reverse charge vendors
     */
    public function scopeReverseCharge($query)
    {
        return $query->where('gst_reverse_charge', 'Yes');
    }

    /**
     * Scope: Indian tax residents
     */
    public function scopeIndianResident($query)
    {
        return $query->where('tax_residency', 'India');
    }
}
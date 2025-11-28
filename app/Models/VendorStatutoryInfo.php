<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class VendorStatutoryInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_statutory_infos';

    protected $fillable = [
        'vendor_id',
        'pan_number',
        'tan_number',
        'gstin',
        'cin',
        'msme_registered',
        'udyam_certificate_path',
        'pan_verified',
        'gst_verified',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'pan_verified' => 'boolean',
        'gst_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Relationship: Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Relationship: Verified by user
     */
    public function verifiedByUser()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get Udyam certificate URL
     */
    public function getUdyamCertificateUrlAttribute()
    {
        return $this->udyam_certificate_path 
            ? Storage::disk('public')->url($this->udyam_certificate_path) 
            : null;
    }

    /**
     * Check if MSME registered
     */
    public function isMsmeRegistered()
    {
        return $this->msme_registered === 'Yes';
    }

    /**
     * Check if fully verified
     */
    public function isFullyVerified()
    {
        return $this->pan_verified && $this->gst_verified;
    }

    /**
     * Scope: Verified records
     */
    public function scopeVerified($query)
    {
        return $query->where('pan_verified', true)->where('gst_verified', true);
    }

    /**
     * Scope: MSME vendors
     */
    public function scopeMsme($query)
    {
        return $query->where('msme_registered', 'Yes');
    }

    /**
     * Scope: Search by PAN
     */
    public function scopeSearchByPan($query, $pan)
    {
        return $query->where('pan_number', 'like', "%{$pan}%");
    }

    /**
     * Scope: Search by GSTIN
     */
    public function scopeSearchByGstin($query, $gstin)
    {
        return $query->where('gstin', 'like', "%{$gstin}%");
    }
}
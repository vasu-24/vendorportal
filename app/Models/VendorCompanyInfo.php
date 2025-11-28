<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorCompanyInfo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_company_infos';

    protected $fillable = [
        'vendor_id',
        'legal_entity_name',
        'business_type',
        'incorporation_date',
        'registered_address',
        'corporate_address',
        'website',
        'parent_company',
    ];

    protected $casts = [
        'incorporation_date' => 'date',
    ];

    /**
     * Relationship: Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get formatted incorporation date
     */
    public function getFormattedIncorporationDateAttribute()
    {
        return $this->incorporation_date ? $this->incorporation_date->format('d M Y') : null;
    }

    /**
     * Scope: By business type
     */
    public function scopeByBusinessType($query, $type)
    {
        return $query->where('business_type', $type);
    }
}
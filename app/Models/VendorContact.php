<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_contacts';

    protected $fillable = [
        'vendor_id',
        'contact_person',
        'designation',
        'mobile',
        'email',
        'alternate_mobile',
        'alternate_email',
        'landline',
    ];

    /**
     * Relationship: Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get formatted mobile with country code
     */
    public function getFormattedMobileAttribute()
    {
        return $this->mobile ? '+91 ' . $this->mobile : null;
    }

    /**
     * Scope: Search by contact person
     */
    public function scopeSearchByPerson($query, $name)
    {
        return $query->where('contact_person', 'like', "%{$name}%");
    }

    /**
     * Scope: Search by mobile
     */
    public function scopeSearchByMobile($query, $mobile)
    {
        return $query->where('mobile', 'like', "%{$mobile}%");
    }
}
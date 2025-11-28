<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class VendorBankDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_bank_details';

    protected $fillable = [
        'vendor_id',
        'bank_name',
        'branch_address',
        'account_holder_name',
        'account_number',
        'ifsc_code',
        'account_type',
        'cancelled_cheque_path',
        'bank_verified',
        'verified_at',
        'verified_by',
        'verification_remarks',
    ];

    protected $casts = [
        'bank_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Hidden fields for security
     */
    protected $hidden = [
        'account_number',
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
     * Get cancelled cheque URL
     */
    public function getCancelledChequeUrlAttribute()
    {
        return $this->cancelled_cheque_path 
            ? Storage::disk('public')->url($this->cancelled_cheque_path) 
            : null;
    }

    /**
     * Get masked account number
     */
    public function getMaskedAccountNumberAttribute()
    {
        if (!$this->account_number) return null;
        
        $length = strlen($this->account_number);
        if ($length <= 4) return $this->account_number;
        
        return str_repeat('*', $length - 4) . substr($this->account_number, -4);
    }

    /**
     * Get full account number (use carefully)
     */
    public function getFullAccountNumber()
    {
        return $this->account_number;
    }

    /**
     * Scope: Verified bank details
     */
    public function scopeVerified($query)
    {
        return $query->where('bank_verified', true);
    }

    /**
     * Scope: Pending verification
     */
    public function scopePendingVerification($query)
    {
        return $query->where('bank_verified', false);
    }

    /**
     * Scope: By bank name
     */
    public function scopeByBank($query, $bankName)
    {
        return $query->where('bank_name', 'like', "%{$bankName}%");
    }
}
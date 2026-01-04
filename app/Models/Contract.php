<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_number',
            'contract_type',      
'is_signed',
        'template_file',
        'company_id',
        'company_name',
        'company_cin',
        'company_address',
        'vendor_id',
        'vendor_name',
        'vendor_cin',
        'vendor_address',
        'start_date',
        'end_date',
        'contract_value',
        'sow_value', 
        'status',
        'is_visible_to_vendor',
        'document_path',
        'notes',
        'created_by',

    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'contract_value' => 'decimal:2',
        'is_visible_to_vendor' => 'boolean',
         'is_signed' => 'boolean',
];
    

    // =====================================================
    // CONSTANTS
    // =====================================================

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT_FOR_SIGNATURE = 'sent_for_signature';
    const STATUS_SIGNED = 'signed';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_TERMINATED = 'terminated';

    const UNITS = [
        'hrs' => 'Hours',
        'days' => 'Days',
        'months' => 'Months',
        'nos' => 'Numbers',
        'lot' => 'Lot',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the organisation/company
     */
    public function company()
    {
        return $this->belongsTo(Organisation::class, 'company_id');
    }

    /**
     * Get contract items/configurations
     */
    public function items()
    {
        return $this->hasMany(ContractItem::class);
    }

    /**
     * Get invoices linked to this contract
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeVisibleToVendor($query)
    {
        return $query->where('is_visible_to_vendor', true);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Generate contract number
     */
    public static function generateContractNumber()
    {
        $year = date('Y');
        $lastContract = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastContract) {
            $lastNumber = intval(substr($lastContract->contract_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'CON-' . $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate contract value from items
     */
    public function calculateContractValue()
    {
        $this->contract_value = $this->items()->sum('amount');
        $this->save();
        return $this->contract_value;
    }

    /**
     * Get formatted contract value
     */
    public function getFormattedContractValueAttribute()
    {
        return '₹' . number_format($this->contract_value, 2);
    }

    /**
     * Get total invoiced amount
     */
    public function getTotalInvoicedAttribute()
    {
        return $this->items()->sum('invoiced_amount');
    }

    /**
     * Get remaining value
     */
    public function getRemainingValueAttribute()
    {
        return $this->contract_value - $this->total_invoiced;
    }

    /**
     * Check if contract is active
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if contract is draft
     */
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'sent_for_signature' => '<span class="badge bg-warning text-dark">Pending Signature</span>',
            'signed' => '<span class="badge bg-info">Signed</span>',
            'active' => '<span class="badge bg-success">Active</span>',
            'expired' => '<span class="badge bg-danger">Expired</span>',
            'terminated' => '<span class="badge bg-dark">Terminated</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . $this->status . '</span>';
    }

// =====================================================
// SCOPES
// =====================================================

public function scopeNormal($query)
{
    return $query->where('contract_type', 'normal');
}

public function scopeAdhoc($query)
{
    return $query->where('contract_type', 'adhoc');
}

}

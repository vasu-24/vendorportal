<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'contract_id',
        'invoice_type',
        'invoice_number',
        'invoice_date',
        'due_date',
        'description',
        'base_total',
        'gst_total',
        'gst_percent',
        'zoho_gst_tax_id',
        'zoho_tds_tax_id',
        'grand_total',
        'tds_percent',
        'tds_amount',
        'net_payable',
        'currency',
        'status',
        'remarks',
        'rejection_reason',
        'include_timesheet',
        'timesheet_path',
        'timesheet_filename',
        'submitted_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'paid_at',
        'reviewed_by',
        'approved_by',
        'rejected_by',
        'zoho_invoice_id',
        'zoho_synced_at',
          'current_approver_role',
    'exceeds_contract',
    'rm_approved_by',
    'rm_approved_at',
    'vp_approved_by',
    'vp_approved_at',
    'ceo_approved_by',
    'ceo_approved_at',
    'finance_approved_by',
    'finance_approved_at',
    'assigned_rm_id',
     'exceed_notes',
'assigned_tag_id',
'assigned_tag_name',
  'rejected_by_role'
];
    

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'base_total' => 'decimal:2',
        'gst_total' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'tds_percent' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
        'include_timesheet' => 'boolean',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
        'zoho_synced_at' => 'datetime',
        'exceeds_contract' => 'boolean',
        'exceed_notes' => 'array',
    'rm_approved_at' => 'datetime',
    'vp_approved_at' => 'datetime',
    'ceo_approved_at' => 'datetime',
    'finance_approved_at' => 'datetime',
    ];

    // =====================================================
    // CONSTANTS
    // =====================================================
    
    const TYPE_NORMAL = 'normal';
    const TYPE_TRAVEL = 'travel';

    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_RESUBMITTED = 'resubmitted';
    const STATUS_PAID = 'paid';
    // 🔥 ADD NEW STATUS CONSTANTS
const STATUS_PENDING_RM = 'pending_rm';
const STATUS_PENDING_VP = 'pending_vp';
const STATUS_PENDING_CEO = 'pending_ceo';
const STATUS_PENDING_FINANCE = 'pending_finance';

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(InvoiceAttachment::class);
    }

    public function invoiceAttachment()
    {
        return $this->hasOne(InvoiceAttachment::class)->where('attachment_type', 'invoice');
    }

    public function travelAttachment()
    {
        return $this->hasOne(InvoiceAttachment::class)->where('attachment_type', 'travel_document');
    }

    public function supportingDocuments()
    {
        return $this->hasMany(InvoiceAttachment::class)->where('attachment_type', 'supporting');
    }

    public function timesheetAttachment()
    {
        return $this->hasOne(InvoiceAttachment::class)->where('attachment_type', 'timesheet');
    }


public function assignedRm()
{
    return $this->belongsTo(User::class, 'assigned_rm_id');
}



    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
    // 🔥 ADD THESE RELATIONSHIPS
public function rmApprovedByUser()
{
    return $this->belongsTo(User::class, 'rm_approved_by');
}

public function vpApprovedByUser()
{
    return $this->belongsTo(User::class, 'vp_approved_by');
}

public function ceoApprovedByUser()
{
    return $this->belongsTo(User::class, 'ceo_approved_by');
}

public function financeApprovedByUser()
{
    return $this->belongsTo(User::class, 'finance_approved_by');
}

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeOfVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('invoice_type', $type);
    }

    public function scopeNormal($query)
    {
        return $query->where('invoice_type', self::TYPE_NORMAL);
    }

    public function scopeTravel($query)
    {
        return $query->where('invoice_type', self::TYPE_TRAVEL);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_RESUBMITTED
        ]);
    }

    public function scopeWithTimesheet($query)
    {
        return $query->where('include_timesheet', true)->whereNotNull('timesheet_path');
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    public function isEditable()
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REJECTED
        ]);
    }

    public function canSubmit()
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REJECTED
        ]);
    }

    public function canReview()
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_RESUBMITTED
        ]);
    }


    // 🔥 ADD THIS METHOD
public function checkExceedsContract()
{
    if (!$this->contract) {
        return false;
    }
    
    return floatval($this->grand_total) > floatval($this->contract->contract_value);
}

    public function isTravel()
    {
        return $this->invoice_type === self::TYPE_TRAVEL;
    }

    public function isNormal()
    {
        return $this->invoice_type === self::TYPE_NORMAL;
    }

    /**
     * Calculate all totals from base total
     */
    public function calculateTotals()
    {
        $this->gst_total = ($this->base_total * $this->gst_percent) / 100;
        $this->grand_total = $this->base_total + $this->gst_total;
        $this->tds_amount = ($this->base_total * $this->tds_percent) / 100;
        $this->net_payable = $this->grand_total - $this->tds_amount;
        
        return $this;
    }

    /**
     * Check if invoice has timesheet
     */
    public function hasTimesheet()
    {
        return $this->include_timesheet && $this->timesheet_path;
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => ['label' => 'Draft', 'color' => 'secondary'],
            'submitted' => ['label' => 'Submitted', 'color' => 'primary'],
            'under_review' => ['label' => 'Under Review', 'color' => 'info'],
            'approved' => ['label' => 'Approved', 'color' => 'success'],
            'rejected' => ['label' => 'Rejected', 'color' => 'danger'],
            'resubmitted' => ['label' => 'Resubmitted', 'color' => 'warning'],
            'paid' => ['label' => 'Paid', 'color' => 'success'],
        ];

        return $labels[$this->status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'normal' => ['label' => 'Normal', 'color' => 'primary'],
            'travel' => ['label' => 'Travel', 'color' => 'info'],
        ];

        return $labels[$this->invoice_type] ?? ['label' => 'Unknown', 'color' => 'secondary'];
    }

    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }

    public function getFormattedBaseTotalAttribute()
    {
        return '₹' . number_format($this->base_total, 2);
    }

    public function getFormattedGstTotalAttribute()
    {
        return '₹' . number_format($this->gst_total, 2);
    }

    public function getFormattedGrandTotalAttribute()
    {
        return '₹' . number_format($this->grand_total, 2);
    }

    public function getFormattedTdsAmountAttribute()
    {
        return '₹' . number_format($this->tds_amount, 2);
    }

    public function getFormattedNetPayableAttribute()
    {
        return '₹' . number_format($this->net_payable, 2);
    }

    public function getTimesheetUrlAttribute()
    {
        if ($this->timesheet_path) {
            return asset('storage/' . $this->timesheet_path);
        }
        return null;
    }

    public function getHasTimesheetAttribute()
    {
        return $this->include_timesheet && $this->timesheet_path;
    }
}
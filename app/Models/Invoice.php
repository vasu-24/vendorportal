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
        'grand_total',
        'currency',
        'status',
        'remarks',
        'rejection_reason',
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
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'base_total' => 'decimal:2',
        'gst_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
        'zoho_synced_at' => 'datetime',
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

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the vendor that owns the invoice
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get the contract associated with this invoice
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Get all line items for this invoice
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get all attachments for this invoice
     */
    public function attachments()
    {
        return $this->hasMany(InvoiceAttachment::class);
    }

    /**
     * Get main invoice attachment
     */
    public function invoiceAttachment()
    {
        return $this->hasOne(InvoiceAttachment::class)->where('attachment_type', 'invoice');
    }

    /**
     * Get travel document attachment
     */
    public function travelAttachment()
    {
        return $this->hasOne(InvoiceAttachment::class)->where('attachment_type', 'travel_document');
    }

    /**
     * Get supporting documents
     */
    public function supportingDocuments()
    {
        return $this->hasMany(InvoiceAttachment::class)->where('attachment_type', 'supporting');
    }

    /**
     * Get user who reviewed
     */
    public function reviewedByUser()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get user who approved
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get user who rejected
     */
    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
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

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if invoice is editable
     */
    public function isEditable()
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REJECTED
        ]);
    }

    /**
     * Check if invoice can be submitted
     */
    public function canSubmit()
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_REJECTED
        ]);
    }

    /**
     * Check if invoice can be approved/rejected
     */
    public function canReview()
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_RESUBMITTED
        ]);
    }

    /**
     * Check if invoice is travel type
     */
    public function isTravel()
    {
        return $this->invoice_type === self::TYPE_TRAVEL;
    }

    /**
     * Check if invoice is normal type
     */
    public function isNormal()
    {
        return $this->invoice_type === self::TYPE_NORMAL;
    }

    /**
     * Get status label with color
     */
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

    /**
     * Get invoice type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'normal' => ['label' => 'Normal', 'color' => 'primary'],
            'travel' => ['label' => 'Travel', 'color' => 'info'],
        ];

        return $labels[$this->invoice_type] ?? ['label' => 'Unknown', 'color' => 'secondary'];
    }

    /**
     * Get line items count
     */
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }

    /**
     * Format base total with currency
     */
    public function getFormattedBaseTotalAttribute()
    {
        return '₹' . number_format($this->base_total, 2);
    }

    /**
     * Format GST total with currency
     */
    public function getFormattedGstTotalAttribute()
    {
        return '₹' . number_format($this->gst_total, 2);
    }

    /**
     * Format grand total with currency
     */
    public function getFormattedGrandTotalAttribute()
    {
        return '₹' . number_format($this->grand_total, 2);
    }
}
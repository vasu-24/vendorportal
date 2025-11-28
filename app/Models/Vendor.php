<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_name',
        'vendor_email',
        'status',
        'token',
        'template_id',
        'email_message',
        'email_sent_at',
        'responded_at',
        'current_step',
        'registration_completed',
        'registration_completed_at',
        'digital_signature',
        'declaration_accurate',
        'declaration_authorized',
        'declaration_terms',
        // Approval fields
        'approval_status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'revision_requested_by',
        'revision_requested_at',
        'revision_notes',
    ];

    protected $casts = [
        'email_sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'registration_completed_at' => 'datetime',
        'registration_completed' => 'boolean',
        'declaration_accurate' => 'boolean',
        'declaration_authorized' => 'boolean',
        'declaration_terms' => 'boolean',
        'current_step' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'revision_requested_at' => 'datetime',
    ];

    /**
     * Approval status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVISION_REQUESTED = 'revision_requested';

    /**
     * Generate unique token
     */
    public static function generateToken()
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Relationship: Template
     */
    public function template()
    {
        return $this->belongsTo(MailTemplate::class, 'template_id');
    }

    /**
     * Relationship: Company Info
     */
    public function companyInfo()
    {
        return $this->hasOne(VendorCompanyInfo::class);
    }

    /**
     * Relationship: Contact
     */
    public function contact()
    {
        return $this->hasOne(VendorContact::class);
    }

    /**
     * Relationship: Statutory Info
     */
    public function statutoryInfo()
    {
        return $this->hasOne(VendorStatutoryInfo::class);
    }

    /**
     * Relationship: Bank Details
     */
    public function bankDetails()
    {
        return $this->hasOne(VendorBankDetail::class);
    }

    /**
     * Relationship: Tax Info
     */
    public function taxInfo()
    {
        return $this->hasOne(VendorTaxInfo::class);
    }

    /**
     * Relationship: Business Profile
     */
    public function businessProfile()
    {
        return $this->hasOne(VendorBusinessProfile::class);
    }

    /**
     * Relationship: Documents (One to Many)
     */
    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    /**
     * Relationship: Approval History
     */
    public function approvalHistory()
    {
        return $this->hasMany(VendorApprovalHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Relationship: Approved by user
     */
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship: Rejected by user
     */
    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Relationship: Revision requested by user
     */
    public function revisionRequestedByUser()
    {
        return $this->belongsTo(User::class, 'revision_requested_by');
    }

    /**
     * Scope: Pending vendors
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Accepted vendors
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope: Rejected vendors
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: Registration completed
     */
    public function scopeRegistrationCompleted($query)
    {
        return $query->where('registration_completed', true);
    }

    /**
     * Scope: Registration pending
     */
    public function scopeRegistrationPending($query)
    {
        return $query->where('registration_completed', false);
    }

    /**
     * Scope: Pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', self::STATUS_PENDING_APPROVAL);
    }

    /**
     * Scope: Approved
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::STATUS_APPROVED);
    }

    /**
     * Scope: Rejected approval
     */
    public function scopeRejectedApproval($query)
    {
        return $query->where('approval_status', self::STATUS_REJECTED);
    }

    /**
     * Scope: Revision requested
     */
    public function scopeRevisionRequested($query)
    {
        return $query->where('approval_status', self::STATUS_REVISION_REQUESTED);
    }

    /**
     * Check if registration is complete
     */
    public function isRegistrationComplete()
    {
        return $this->registration_completed === true;
    }

    /**
     * Check if pending approval
     */
    public function isPendingApproval()
    {
        return $this->approval_status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Check if approved
     */
    public function isApproved()
    {
        return $this->approval_status === self::STATUS_APPROVED;
    }

    /**
     * Check if rejected
     */
    public function isRejected()
    {
        return $this->approval_status === self::STATUS_REJECTED;
    }

    /**
     * Check if revision requested
     */
    public function isRevisionRequested()
    {
        return $this->approval_status === self::STATUS_REVISION_REQUESTED;
    }

    /**
     * Get approval status label with color
     */
    public function getApprovalStatusLabelAttribute()
    {
        $labels = [
            'draft' => ['label' => 'Draft', 'color' => 'secondary'],
            'pending_approval' => ['label' => 'Pending Approval', 'color' => 'warning'],
            'approved' => ['label' => 'Approved', 'color' => 'success'],
            'rejected' => ['label' => 'Rejected', 'color' => 'danger'],
            'revision_requested' => ['label' => 'Revision Requested', 'color' => 'info'],
        ];

        return $labels[$this->approval_status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
    }

    /**
     * Get registration progress percentage
     */
    public function getRegistrationProgress()
    {
        return ($this->current_step / 4) * 100;
    }

    /**
     * Load all registration data
     */
    public function loadFullRegistration()
    {
        return $this->load([
            'companyInfo',
            'contact',
            'statutoryInfo',
            'bankDetails',
            'taxInfo',
            'businessProfile',
            'documents',
            'approvalHistory'
        ]);
    }

    /**
     * Add history entry
     */
    public function addHistory($action, $actionByType, $actionById, $actionByName, $notes = null, $dataSnapshot = null, $changedFields = null)
    {
        return VendorApprovalHistory::createEntry(
            $this->id,
            $action,
            $actionByType,
            $actionById,
            $actionByName,
            $notes,
            $dataSnapshot,
            $changedFields
        );
    }
}
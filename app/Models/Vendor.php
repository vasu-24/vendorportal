<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Vendor extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $fillable = [
        'vendor_name',
        'vendor_email',
        'password',
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
        'approval_status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'revision_requested_by',
        'revision_requested_at',
        'revision_notes',
        'link_expires_at',     
        'zoho_contact_id',       
        'zoho_synced_at',   
          'has_travel_access',  
          
          
    ];

    protected $hidden = [
        'password',
        'remember_token',
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
        'link_expires_at' => 'datetime',    // 🔥 Added
        'zoho_synced_at' => 'datetime',     // 🔥 Added
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVISION_REQUESTED = 'revision_requested';

    /**
     * Generate unique token for vendor
     */
    public static function generateToken()
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function template()
    {
        return $this->belongsTo(MailTemplate::class, 'template_id');
    }

    public function companyInfo()
    {
        return $this->hasOne(VendorCompanyInfo::class);
    }

    public function contact()
    {
        return $this->hasOne(VendorContact::class);
    }

    public function statutoryInfo()
    {
        return $this->hasOne(VendorStatutoryInfo::class);
    }

    public function bankDetails()
    {
        return $this->hasOne(VendorBankDetail::class);
    }

    public function taxInfo()
    {
        return $this->hasOne(VendorTaxInfo::class);
    }

    public function businessProfile()
    {
        return $this->hasOne(VendorBusinessProfile::class);
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function approvalHistory()
    {
        return $this->hasMany(VendorApprovalHistory::class)->orderBy('created_at', 'desc');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function revisionRequestedByUser()
    {
        return $this->belongsTo(User::class, 'revision_requested_by');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeRegistrationCompleted($query)
    {
        return $query->where('registration_completed', true);
    }

    public function scopeRegistrationPending($query)
    {
        return $query->where('registration_completed', false);
    }

    /**
 * Get travel batches for this vendor
 */
public function travelBatches()
{
    return $this->hasMany(TravelBatch::class, 'vendor_id');
}

    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', self::STATUS_PENDING_APPROVAL);
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::STATUS_APPROVED);
    }

    public function scopeRejectedApproval($query)
    {
        return $query->where('approval_status', self::STATUS_REJECTED);
    }

    public function scopeRevisionRequested($query)
    {
        return $query->where('approval_status', self::STATUS_REVISION_REQUESTED);
    }

    // 🔥 Zoho Scopes
    public function scopeSyncedToZoho($query)
    {
        return $query->whereNotNull('zoho_contact_id');
    }

    public function scopeNotSyncedToZoho($query)
    {
        return $query->whereNull('zoho_contact_id');
    }

    // =====================================================
    // STATUS CHECK METHODS
    // =====================================================

    public function isRegistrationComplete()
    {
        return $this->registration_completed === true;
    }

    public function isPendingApproval()
    {
        return $this->approval_status === self::STATUS_PENDING_APPROVAL;
    }

    public function isApproved()
    {
        return $this->approval_status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->approval_status === self::STATUS_REJECTED;
    }

    public function isRevisionRequested()
    {
        return $this->approval_status === self::STATUS_REVISION_REQUESTED;
    }

    // 🔥 Zoho Check Methods
    public function isSyncedToZoho()
    {
        return !empty($this->zoho_contact_id);
    }

    public function isLinkExpired()
    {
        if (!$this->link_expires_at) {
            return false;
        }
        return now()->isAfter($this->link_expires_at);
    }

    // =====================================================
    // ATTRIBUTES
    // =====================================================

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

    // 🔥 Zoho Status Label
    public function getZohoStatusLabelAttribute()
    {
        if ($this->zoho_contact_id) {
            return ['label' => 'Synced', 'color' => 'success'];
        }
        return ['label' => 'Not Synced', 'color' => 'secondary'];
    }

    public function getRegistrationProgress()
    {
        return ($this->current_step / 4) * 100;
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

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
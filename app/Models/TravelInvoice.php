<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\ManagerTag;
use Illuminate\Support\Facades\Log;

class TravelInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Batch & Vendor
        'batch_id',
        'vendor_id',
        'employee_id',
        
        // Invoice Details
        'invoice_number',
        'invoice_type',
        'invoice_date',
        'reference_invoice_id',
        
        // Project/Tag
        'tag_id',
        'tag_name',
        'project_code',
        
        // Travel Details
        'location',
        'travel_type',
        'travel_date',
        'description',
        
        // Amounts
        'basic_total',
        'taxes_total',
        'service_charge_total',
        'gst_total',
        'gross_amount',
        'tds_percent',
        'tds_amount',
        'net_amount',
        
        // Status & Approval
        'status',
        'current_approver_role',
        'assigned_rm_id',
        
        // Approval Tracking
        'rm_approved_by',
        'rm_approved_at',
        'vp_approved_by',
        'vp_approved_at',
        'vp_pending_since',
        'ceo_approved_by',
        'ceo_approved_at',
        'finance_approved_by',
        'finance_approved_at',
        
        // Final Approval
        'approved_by',
        'approved_at',
        
        // Rejection
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'rejected_by_role',
        
        // Auto Escalation
        'auto_escalated',
        'auto_escalated_at',
        'escalation_reason',
        
        // Timestamps
        'submitted_at',
        'paid_at',
        
        // Zoho
        'zoho_bill_id',
        'zoho_synced_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'travel_date' => 'date',
        'basic_total' => 'decimal:2',
        'taxes_total' => 'decimal:2',
        'service_charge_total' => 'decimal:2',
        'gst_total' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'tds_percent' => 'decimal:2',
        'tds_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'auto_escalated' => 'boolean',
        'submitted_at' => 'datetime',
        'rm_approved_at' => 'datetime',
        'vp_approved_at' => 'datetime',
        'vp_pending_since' => 'datetime',
        'ceo_approved_at' => 'datetime',
        'finance_approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'auto_escalated_at' => 'datetime',
        'paid_at' => 'datetime',
        'zoho_synced_at' => 'datetime',
    ];

    // =====================================================
    // CONSTANTS
    // =====================================================
    
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_RESUBMITTED = 'resubmitted';
    const STATUS_PENDING_RM = 'pending_rm';
    const STATUS_PENDING_VP = 'pending_vp';
    const STATUS_PENDING_CEO = 'pending_ceo';
    const STATUS_PENDING_FINANCE = 'pending_finance';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    // Approval Flow Configuration
    const APPROVAL_FLOW = [
        'pending_rm' => [
            'role' => 'manager',
            'next_status' => 'pending_vp',
            'approved_by_field' => 'rm_approved_by',
            'approved_at_field' => 'rm_approved_at',
            'batch_method' => 'markRmApproved',
        ],
        'pending_vp' => [
            'role' => 'vp',
            'next_status' => 'pending_finance', // CEO only if 7 days timeout
            'approved_by_field' => 'vp_approved_by',
            'approved_at_field' => 'vp_approved_at',
            'batch_method' => 'markVpApproved',
        ],
        'pending_ceo' => [
            'role' => 'ceo',
            'next_status' => 'pending_finance',
            'approved_by_field' => 'ceo_approved_by',
            'approved_at_field' => 'ceo_approved_at',
            'batch_method' => 'markCeoApproved',
        ],
        'pending_finance' => [
            'role' => 'finance',
            'next_status' => 'approved',
            'approved_by_field' => 'finance_approved_by',
            'approved_at_field' => 'finance_approved_at',
            'batch_method' => 'markFinanceApproved',
        ],
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function batch()
    {
        return $this->belongsTo(TravelBatch::class, 'batch_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function employee()
    {
        return $this->belongsTo(TravelEmployee::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(TravelInvoiceItem::class, 'travel_invoice_id');
    }

    public function bills()
    {
        return $this->hasMany(TravelInvoiceBill::class, 'travel_invoice_id');
    }

    public function assignedRm()
    {
        return $this->belongsTo(User::class, 'assigned_rm_id');
    }

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

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // =====================================================
    // APPROVAL METHODS - CLEAN APPROACH
    // =====================================================

    /**
     * Start Review - Move from submitted to pending_rm
     */
    public function startReview($userId = null)
    {
        if (!$this->canStartReview()) {
            return false;
        }

        // Auto-assign RM based on employee's tag
        $assignedRmId = $userId;
        if ($this->employee) {
            $empRmId = $this->employee->getManagerId();
            if ($empRmId) {
                $assignedRmId = $empRmId;
            }
        }

        $this->update([
            'status' => self::STATUS_PENDING_RM,
            'current_approver_role' => 'rm',
            'assigned_rm_id' => $assignedRmId,
            'rejection_reason' => null,
        ]);

        // Update batch status
        if ($this->batch) {
            $this->batch->updateStatus();
        }

        return true;
    }

    /**
     * Check if can start review
     */
    public function canStartReview()
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_RESUBMITTED]);
    }

    /**
     * Check if user can approve this invoice
     */
    public function canBeApprovedBy($user)
    {
        $role = $user->role->slug ?? 'viewer';
        
        // Super admin can approve anything
        if ($role === 'super-admin') {
            return in_array($this->status, [
                self::STATUS_SUBMITTED,
                self::STATUS_RESUBMITTED,
                self::STATUS_PENDING_RM,
                self::STATUS_PENDING_VP,
                self::STATUS_PENDING_CEO,
                self::STATUS_PENDING_FINANCE,
            ]);
        }

        // Check role-specific permissions
        $allowedStatuses = $this->getAllowedStatusesForRole($role);
        
        if (!in_array($this->status, $allowedStatuses)) {
            return false;
        }

        // For manager, also check if assigned to them
        if ($role === 'manager') {
            $userTagIds = ManagerTag::where('user_id', $user->id)->pluck('tag_id')->toArray();
            if ($this->assigned_rm_id !== $user->id && !in_array($this->tag_id, $userTagIds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user can reject this invoice
     */
    public function canBeRejectedBy($user)
    {
        return $this->canBeApprovedBy($user);
    }

    /**
     * Get allowed statuses for role
     */
    public function getAllowedStatusesForRole($role)
    {
        $map = [
            'manager' => [self::STATUS_SUBMITTED, self::STATUS_RESUBMITTED, self::STATUS_PENDING_RM],
            'vp' => [self::STATUS_PENDING_VP],
            'ceo' => [self::STATUS_PENDING_CEO],
            'finance' => [self::STATUS_PENDING_FINANCE],
            'super-admin' => [
                self::STATUS_SUBMITTED,
                self::STATUS_RESUBMITTED,
                self::STATUS_PENDING_RM,
                self::STATUS_PENDING_VP,
                self::STATUS_PENDING_CEO,
                self::STATUS_PENDING_FINANCE,
            ],
        ];

        return $map[$role] ?? [];
    }

    /**
     * Approve invoice
     */
    public function approve($user)
    {
        if (!$this->canBeApprovedBy($user)) {
            return [
                'success' => false,
                'message' => 'You cannot approve this invoice at this stage.',
            ];
        }

        $userId = $user->id;
        $currentStatus = $this->status;

        // Handle submitted/resubmitted - Start Review
        if (in_array($currentStatus, [self::STATUS_SUBMITTED, self::STATUS_RESUBMITTED])) {
            $this->startReview($userId);
            return [
                'success' => true,
                'message' => 'Invoice sent to RM for approval.',
                'new_status' => self::STATUS_PENDING_RM,
            ];
        }

        // Get flow configuration
        $flowConfig = self::APPROVAL_FLOW[$currentStatus] ?? null;
        if (!$flowConfig) {
            return [
                'success' => false,
                'message' => 'Invalid status for approval.',
            ];
        }

        $nextStatus = $flowConfig['next_status'];
        $updateData = [
            'status' => $nextStatus,
            'current_approver_role' => $this->getNextApproverRole($nextStatus),
            $flowConfig['approved_by_field'] => $userId,
            $flowConfig['approved_at_field'] => now(),
        ];

        // Set vp_pending_since when moving to pending_vp
        if ($nextStatus === self::STATUS_PENDING_VP) {
            $updateData['vp_pending_since'] = now();
        }

        // Final approval
        if ($nextStatus === self::STATUS_APPROVED) {
            $updateData['approved_by'] = $userId;
            $updateData['approved_at'] = now();
        }

        $this->update($updateData);

        // Update batch timestamps and status
        if ($this->batch) {
            // Call batch method to update timestamp
            if (isset($flowConfig['batch_method'])) {
                $method = $flowConfig['batch_method'];
                $this->batch->$method();
            }
            
            // If all invoices approved, mark batch as approved
            if ($nextStatus === self::STATUS_APPROVED) {
                $this->batch->markApproved();
            }
            
            $this->batch->updateStatus();
        }

        // Log the approval
        Log::info('Travel Invoice approved', [
            'invoice_id' => $this->id,
            'from_status' => $currentStatus,
            'to_status' => $nextStatus,
            'approved_by' => $userId,
        ]);

        return [
            'success' => true,
            'message' => $this->getApprovalMessage($currentStatus, $nextStatus),
            'new_status' => $nextStatus,
        ];
    }

    /**
     * Reject invoice
     */
    public function reject($user, $reason)
    {
        if (!$this->canBeRejectedBy($user)) {
            return [
                'success' => false,
                'message' => 'You cannot reject this invoice at this stage.',
            ];
        }

        $rejectedByRole = $this->getRoleLabel($user->role->slug ?? 'viewer');
        $previousStatus = $this->status;

        $this->update([
            'status' => self::STATUS_REJECTED,
            'current_approver_role' => null,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
            'rejected_by_role' => $rejectedByRole,
        ]);

        // Update batch status and totals
        if ($this->batch) {
            $this->batch->updateStatus();
            $this->batch->updateTotals(); // Recalculate totals without rejected invoices
        }

        // Log the rejection
        Log::info('Travel Invoice rejected', [
            'invoice_id' => $this->id,
            'previous_status' => $previousStatus,
            'rejected_by' => $user->id,
            'rejected_by_role' => $rejectedByRole,
            'reason' => $reason,
        ]);

        return [
            'success' => true,
            'message' => "Invoice rejected by {$rejectedByRole}. Sent back to vendor.",
        ];
    }

    /**
     * Get next approver role
     */
    private function getNextApproverRole($status)
    {
        $map = [
            self::STATUS_PENDING_RM => 'rm',
            self::STATUS_PENDING_VP => 'vp',
            self::STATUS_PENDING_CEO => 'ceo',
            self::STATUS_PENDING_FINANCE => 'finance',
        ];

        return $map[$status] ?? null;
    }

    /**
     * Get approval message
     */
    private function getApprovalMessage($from, $to)
    {
        $messages = [
            'pending_rm' => '✅ Invoice sent to RM for review.',
            'pending_vp' => '✅ RM approved! Invoice sent to VOO.',
            'pending_ceo' => '✅ Escalated to CEO for approval.',
            'pending_finance' => '✅ Approved! Invoice sent to Finance.',
            'approved' => '✅ Invoice approved by Finance!',
        ];

        return $messages[$to] ?? 'Invoice status updated.';
    }

    /**
     * Get role label
     */
    private function getRoleLabel($role)
    {
        $labels = [
            'manager' => 'RM',
            'vp' => 'VOO',
            'ceo' => 'CEO',
            'finance' => 'Finance',
            'super-admin' => 'Super Admin',
        ];

        return $labels[$role] ?? 'Unknown';
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if invoice is editable
     */
    public function isEditable()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    /**
     * Calculate totals from items
     */
    public function calculateTotals()
    {
        $this->basic_total = $this->items()->sum('basic');
        $this->taxes_total = $this->items()->sum('taxes');
        $this->service_charge_total = $this->items()->sum('service_charge');
        $this->gst_total = $this->items()->sum('gst');
        $this->gross_amount = $this->items()->sum('gross_amount');
        $this->tds_amount = ($this->basic_total * $this->tds_percent) / 100;
        $this->net_amount = $this->gross_amount - $this->tds_amount;
        
        return $this;
    }

    /**
     * Get approval timeline for display
     */
    public function getApprovalTimeline()
    {
        $timeline = [];

        if ($this->submitted_at) {
            $timeline[] = [
                'label' => 'Submitted',
                'date' => $this->submitted_at,
                'status' => 'done',
            ];
        }

        if ($this->rm_approved_at) {
            $timeline[] = [
                'label' => 'RM Approved',
                'user' => $this->rmApprovedByUser->name ?? null,
                'date' => $this->rm_approved_at,
                'status' => 'done',
            ];
        } elseif ($this->status === self::STATUS_PENDING_RM) {
            $timeline[] = [
                'label' => 'Pending RM',
                'user' => $this->assignedRm->name ?? null,
                'status' => 'current',
            ];
        }

        if ($this->vp_approved_at) {
            $timeline[] = [
                'label' => 'VOO Approved',
                'user' => $this->vpApprovedByUser->name ?? null,
                'date' => $this->vp_approved_at,
                'status' => 'done',
            ];
        } elseif ($this->status === self::STATUS_PENDING_VP) {
            $timeline[] = [
                'label' => 'Pending VOO',
                'status' => 'current',
            ];
        }

        if ($this->ceo_approved_at) {
            $timeline[] = [
                'label' => 'CEO Approved',
                'user' => $this->ceoApprovedByUser->name ?? null,
                'date' => $this->ceo_approved_at,
                'status' => 'done',
            ];
        } elseif ($this->status === self::STATUS_PENDING_CEO) {
            $timeline[] = [
                'label' => 'Pending CEO',
                'status' => 'current',
                'note' => $this->auto_escalated ? 'Auto-escalated from VOO' : null,
            ];
        }

        if ($this->finance_approved_at) {
            $timeline[] = [
                'label' => 'Finance Approved',
                'user' => $this->financeApprovedByUser->name ?? null,
                'date' => $this->finance_approved_at,
                'status' => 'done',
            ];
        } elseif ($this->status === self::STATUS_PENDING_FINANCE) {
            $timeline[] = [
                'label' => 'Pending Finance',
                'status' => 'current',
            ];
        }

        if ($this->status === self::STATUS_REJECTED) {
            $timeline[] = [
                'label' => 'Rejected',
                'user' => $this->rejectedByUser->name ?? null,
                'date' => $this->rejected_at,
                'reason' => $this->rejection_reason,
                'status' => 'rejected',
            ];
        }

        if ($this->status === self::STATUS_APPROVED) {
            $timeline[] = [
                'label' => 'Approved',
                'date' => $this->approved_at,
                'status' => 'done',
            ];
        }

        if ($this->status === self::STATUS_PAID) {
            $timeline[] = [
                'label' => 'Paid',
                'date' => $this->paid_at,
                'status' => 'done',
            ];
        }

        return $timeline;
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUBMITTED,
            self::STATUS_RESUBMITTED,
            self::STATUS_PENDING_RM,
            self::STATUS_PENDING_VP,
            self::STATUS_PENDING_CEO,
            self::STATUS_PENDING_FINANCE,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', self::STATUS_REJECTED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForRole($query, $role, $userId = null)
    {
        if ($role === 'super-admin') {
            return $query;
        }

        $statusMap = [
            'manager' => [self::STATUS_SUBMITTED, self::STATUS_RESUBMITTED, self::STATUS_PENDING_RM],
            'vp' => [self::STATUS_PENDING_VP],
            'ceo' => [self::STATUS_PENDING_CEO],
            'finance' => [self::STATUS_PENDING_FINANCE],
        ];

        $statuses = $statusMap[$role] ?? [];

        if ($role === 'manager' && $userId) {
            $userTagIds = ManagerTag::where('user_id', $userId)->pluck('tag_id')->toArray();
            return $query->whereIn('status', $statuses)
                ->where(function ($q) use ($userId, $userTagIds) {
                    $q->where('assigned_rm_id', $userId)
                      ->orWhereIn('tag_id', $userTagIds);
                });
        }

        return $query->whereIn('status', $statuses);
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => ['label' => 'Draft', 'color' => 'secondary'],
            'submitted' => ['label' => 'Submitted', 'color' => 'primary'],
            'resubmitted' => ['label' => 'Resubmitted', 'color' => 'warning'],
            'pending_rm' => ['label' => 'Pending RM', 'color' => 'info'],
            'pending_vp' => ['label' => 'Pending VOO', 'color' => 'info'],
            'pending_ceo' => ['label' => 'Pending CEO', 'color' => 'info'],
            'pending_finance' => ['label' => 'Pending Finance', 'color' => 'info'],
            'approved' => ['label' => 'Approved', 'color' => 'success'],
            'rejected' => ['label' => 'Rejected', 'color' => 'danger'],
            'paid' => ['label' => 'Paid', 'color' => 'success'],
        ];

        return $labels[$this->status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_number',
        'vendor_id',
        'total_invoices',
        'total_amount',
        'status',
        'current_approver_role',
        'rejected_by_role',
        'submitted_at',
        
        // Approval tracking
        'rm_approved_at',
        'vp_approved_at',
        'ceo_approved_at',
        'finance_approved_at',
        'approved_at',
        'rejected_at',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'rm_approved_at' => 'datetime',
        'vp_approved_at' => 'datetime',
        'ceo_approved_at' => 'datetime',
        'finance_approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // =====================================================
    // CONSTANTS - ALL STATUSES
    // =====================================================
    
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_PENDING_RM = 'pending_rm';
    const STATUS_PENDING_VP = 'pending_vp';
    const STATUS_PENDING_CEO = 'pending_ceo';
    const STATUS_PENDING_FINANCE = 'pending_finance';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function invoices()
    {
        return $this->hasMany(TravelInvoice::class, 'batch_id');
    }

    // Active invoices (not rejected)
    public function activeInvoices()
    {
        return $this->hasMany(TravelInvoice::class, 'batch_id')->where('status', '!=', 'rejected');
    }

    // Rejected invoices
    public function rejectedInvoices()
    {
        return $this->hasMany(TravelInvoice::class, 'batch_id')->where('status', 'rejected');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeOfVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SUBMITTED,
            self::STATUS_PENDING_RM,
            self::STATUS_PENDING_VP,
            self::STATUS_PENDING_CEO,
            self::STATUS_PENDING_FINANCE,
        ]);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Generate next batch number for vendor
     */
    public static function generateBatchNumber($vendorId)
    {
        $lastBatch = self::where('vendor_id', $vendorId)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastBatch) {
            $lastNumber = intval(str_replace('BATCH-', '', $lastBatch->batch_number));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'BATCH-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Update batch totals from ACTIVE invoices only (not rejected)
     */
    public function updateTotals()
    {
        $activeInvoices = $this->invoices()->where('status', '!=', 'rejected');
        
        $this->total_invoices = $this->invoices()->count(); // Total including rejected
        $this->total_amount = $activeInvoices->sum('gross_amount'); // Only active amount
        $this->save();
        
        return $this;
    }

    /**
     * Update batch status based on invoices
     * IGNORES rejected invoices when calculating status
     */
    public function updateStatus()
    {
        $invoices = $this->invoices;
        $totalCount = $invoices->count();
        
        if ($totalCount === 0) {
            $this->status = self::STATUS_DRAFT;
            $this->current_approver_role = null;
            $this->save();
            return $this;
        }

        // Separate rejected from active invoices
        $rejectedCount = $invoices->where('status', 'rejected')->count();
        $activeInvoices = $invoices->where('status', '!=', 'rejected');
        $activeCount = $activeInvoices->count();

        // If ALL invoices are rejected
        if ($activeCount === 0 && $rejectedCount > 0) {
            $this->status = self::STATUS_REJECTED;
            $this->current_approver_role = null;
            $this->rejected_at = $this->rejected_at ?? now();
            $this->save();
            return $this;
        }

        // Count by status (excluding rejected)
        $statusCounts = [
            'submitted' => $activeInvoices->where('status', 'submitted')->count(),
            'resubmitted' => $activeInvoices->where('status', 'resubmitted')->count(),
            'pending_rm' => $activeInvoices->where('status', 'pending_rm')->count(),
            'pending_vp' => $activeInvoices->where('status', 'pending_vp')->count(),
            'pending_ceo' => $activeInvoices->where('status', 'pending_ceo')->count(),
            'pending_finance' => $activeInvoices->where('status', 'pending_finance')->count(),
            'approved' => $activeInvoices->where('status', 'approved')->count(),
            'paid' => $activeInvoices->where('status', 'paid')->count(),
        ];

        // Determine batch status based on active invoices
        $newStatus = $this->calculateBatchStatus($statusCounts, $activeCount);
        $this->status = $newStatus;
        $this->current_approver_role = $this->getApproverRoleForStatus($newStatus);
        
        // Update totals (only active invoices)
        $this->total_amount = $activeInvoices->sum('gross_amount');
        
        $this->save();
        return $this;
    }

    /**
     * Calculate batch status from invoice counts
     */
    private function calculateBatchStatus($counts, $activeCount)
    {
        // All same status = batch gets that status
        if ($counts['submitted'] + $counts['resubmitted'] === $activeCount) {
            return self::STATUS_SUBMITTED;
        }
        if ($counts['pending_rm'] === $activeCount) {
            return self::STATUS_PENDING_RM;
        }
        if ($counts['pending_vp'] === $activeCount) {
            return self::STATUS_PENDING_VP;
        }
        if ($counts['pending_ceo'] === $activeCount) {
            return self::STATUS_PENDING_CEO;
        }
        if ($counts['pending_finance'] === $activeCount) {
            return self::STATUS_PENDING_FINANCE;
        }
        if ($counts['approved'] === $activeCount) {
            return self::STATUS_APPROVED;
        }
        if ($counts['paid'] === $activeCount) {
            return self::STATUS_PAID;
        }

        // Mixed statuses - find the LOWEST pending status
        if ($counts['submitted'] + $counts['resubmitted'] > 0) {
            return self::STATUS_SUBMITTED;
        }
        if ($counts['pending_rm'] > 0) {
            return self::STATUS_PENDING_RM;
        }
        if ($counts['pending_vp'] > 0) {
            return self::STATUS_PENDING_VP;
        }
        if ($counts['pending_ceo'] > 0) {
            return self::STATUS_PENDING_CEO;
        }
        if ($counts['pending_finance'] > 0) {
            return self::STATUS_PENDING_FINANCE;
        }

        // Mix of approved/paid
        if ($counts['approved'] > 0 || $counts['paid'] > 0) {
            return self::STATUS_PARTIAL;
        }

        return self::STATUS_SUBMITTED;
    }

    /**
     * Get approver role for status
     */
    private function getApproverRoleForStatus($status)
    {
        $roleMap = [
            'submitted' => 'rm',
            'pending_rm' => 'rm',
            'pending_vp' => 'vp',
            'pending_ceo' => 'ceo',
            'pending_finance' => 'finance',
        ];

        return $roleMap[$status] ?? null;
    }

    // =====================================================
    // TIMESTAMP UPDATE METHODS
    // =====================================================

    /**
     * Update RM approved timestamp
     */
    public function markRmApproved()
    {
        if (!$this->rm_approved_at) {
            $this->rm_approved_at = now();
            $this->save();
        }
        return $this;
    }

    /**
     * Update VP approved timestamp
     */
    public function markVpApproved()
    {
        if (!$this->vp_approved_at) {
            $this->vp_approved_at = now();
            $this->save();
        }
        return $this;
    }

    /**
     * Update CEO approved timestamp
     */
    public function markCeoApproved()
    {
        if (!$this->ceo_approved_at) {
            $this->ceo_approved_at = now();
            $this->save();
        }
        return $this;
    }

    /**
     * Update Finance approved timestamp
     */
    public function markFinanceApproved()
    {
        if (!$this->finance_approved_at) {
            $this->finance_approved_at = now();
            $this->save();
        }
        return $this;
    }

    /**
     * Mark batch as fully approved
     */
    public function markApproved()
    {
        if (!$this->approved_at) {
            $this->approved_at = now();
            $this->save();
        }
        return $this;
    }

    /**
     * Mark batch as paid
     */
    public function markPaid()
    {
        if (!$this->paid_at) {
            $this->paid_at = now();
            $this->status = self::STATUS_PAID;
            $this->save();
        }
        return $this;
    }

    // =====================================================
    // APPROVAL HELPER METHODS
    // =====================================================

    /**
     * Get count of invoices pending at specific status
     */
    public function getInvoiceCountByStatus($status)
    {
        return $this->invoices()->where('status', $status)->count();
    }

    /**
     * Get count of invoices that can be approved by role
     */
    public function getApprovableCountForRole($role, $userId = null)
    {
        $statusMap = [
            'manager' => ['submitted', 'resubmitted', 'pending_rm'],
            'vp' => ['pending_vp'],
            'ceo' => ['pending_ceo'],
            'finance' => ['pending_finance'],
            'super-admin' => ['submitted', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance'],
        ];

        $statuses = $statusMap[$role] ?? [];
        
        if (empty($statuses)) {
            return 0;
        }

        $query = $this->invoices()->whereIn('status', $statuses);
        
        // Filter by manager's tags if manager role
        if ($role === 'manager' && $userId) {
            $managerTagIds = ManagerTag::where('user_id', $userId)->pluck('tag_id')->toArray();
            $query->whereIn('tag_id', $managerTagIds);
        }

        return $query->count();
    }

    /**
     * Get invoices that can be approved by role
     */
    public function getApprovableInvoicesForRole($role, $userId = null)
    {
        $statusMap = [
            'manager' => ['submitted', 'resubmitted', 'pending_rm'],
            'vp' => ['pending_vp'],
            'ceo' => ['pending_ceo'],
            'finance' => ['pending_finance'],
            'super-admin' => ['submitted', 'resubmitted', 'pending_rm', 'pending_vp', 'pending_ceo', 'pending_finance'],
        ];

        $statuses = $statusMap[$role] ?? [];
        
        $query = $this->invoices()->whereIn('status', $statuses);
        
        // Filter by manager's tags if manager role
        if ($role === 'manager' && $userId) {
            $managerTagIds = ManagerTag::where('user_id', $userId)->pluck('tag_id')->toArray();
            $query->whereIn('tag_id', $managerTagIds);
        }
        
        return $query->get();
    }

    /**
     * Check if batch can be reviewed (Start Review)
     */
    public function canStartReview()
    {
        return $this->invoices()
            ->whereIn('status', ['submitted', 'resubmitted'])
            ->exists();
    }

    /**
     * Check if user can take action on this batch
     */
    public function canUserTakeAction($user)
    {
        $role = $user->role->slug ?? 'viewer';
        return $this->getApprovableCountForRole($role) > 0;
    }

    // =====================================================
    // SUMMARY METHODS
    // =====================================================

    /**
     * Get batch summary for display
     */
    public function getSummary()
    {
        $invoices = $this->invoices;
        $activeInvoices = $invoices->where('status', '!=', 'rejected');
        $rejectedInvoices = $invoices->where('status', 'rejected');
        
        return [
            'total' => $invoices->count(),
            'active_count' => $activeInvoices->count(),
            'rejected_count' => $rejectedInvoices->count(),
            
            'submitted' => $invoices->whereIn('status', ['submitted', 'resubmitted'])->count(),
            'pending_rm' => $invoices->where('status', 'pending_rm')->count(),
            'pending_vp' => $invoices->where('status', 'pending_vp')->count(),
            'pending_ceo' => $invoices->where('status', 'pending_ceo')->count(),
            'pending_finance' => $invoices->where('status', 'pending_finance')->count(),
            'approved' => $invoices->where('status', 'approved')->count(),
            'rejected' => $rejectedInvoices->count(),
            'paid' => $invoices->where('status', 'paid')->count(),
            
            // Amounts - ACTIVE ONLY
            'total_amount' => $activeInvoices->sum('gross_amount'),
            'active_amount' => $activeInvoices->sum('gross_amount'),
            'rejected_amount' => $rejectedInvoices->sum('gross_amount'),
            'approved_amount' => $invoices->whereIn('status', ['approved', 'paid'])->sum('gross_amount'),
            'pending_amount' => $activeInvoices->whereNotIn('status', ['approved', 'paid'])->sum('gross_amount'),
            
            // Breakdown - ACTIVE ONLY
            'basic_total' => $activeInvoices->sum('basic_total'),
            'taxes_total' => $activeInvoices->sum('taxes_total'),
            'service_charge_total' => $activeInvoices->sum('service_charge_total'),
            'gst_total' => $activeInvoices->sum('gst_total'),
            'gross_total' => $activeInvoices->sum('gross_amount'),
            'tds_total' => $activeInvoices->sum('tds_amount'),
            'net_total' => $activeInvoices->sum('net_amount'),
        ];
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => ['label' => 'Draft', 'color' => 'secondary'],
            'submitted' => ['label' => 'Submitted', 'color' => 'primary'],
            'pending_rm' => ['label' => 'Pending RM', 'color' => 'warning'],
            'pending_vp' => ['label' => 'Pending VOO', 'color' => 'info'],
            'pending_ceo' => ['label' => 'Pending CEO', 'color' => 'danger'],
            'pending_finance' => ['label' => 'Pending Finance', 'color' => 'success'],
            'approved' => ['label' => 'Approved', 'color' => 'success'],
            'rejected' => ['label' => 'Rejected', 'color' => 'danger'],
            'paid' => ['label' => 'Paid', 'color' => 'primary'],
            'partial' => ['label' => 'Partial', 'color' => 'warning'],
        ];

        return $labels[$this->status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
    }

    public function getFormattedTotalAmountAttribute()
    {
        return '₹' . number_format($this->total_amount, 2);
    }
}
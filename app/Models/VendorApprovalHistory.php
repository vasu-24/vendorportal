<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorApprovalHistory extends Model
{
    use HasFactory;

    protected $table = 'vendor_approval_history';

    protected $fillable = [
        'vendor_id',
        'action',
        'action_by_type',
        'action_by_id',
        'action_by_name',
        'notes',
        'ip_address',
        'user_agent',
        'data_snapshot',
        'changed_fields',
    ];

    protected $casts = [
        'data_snapshot' => 'array',
        'changed_fields' => 'array',
    ];

    /**
     * Action constants
     */
    const ACTION_SUBMITTED = 'submitted';
    const ACTION_PENDING_APPROVAL = 'pending_approval';
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_REVISION_REQUESTED = 'revision_requested';
    const ACTION_RESUBMITTED = 'resubmitted';
    const ACTION_DATA_UPDATED = 'data_updated';

    /**
     * Action by type constants
     */
    const TYPE_USER = 'user';
    const TYPE_VENDOR = 'vendor';

    /**
     * Relationship: Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Relationship: Action by user
     */
    public function actionByUser()
    {
        return $this->belongsTo(User::class, 'action_by_id');
    }

    /**
     * Get action label with icon
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'submitted' => ['label' => 'Submitted', 'icon' => 'bi-upload', 'color' => 'primary'],
            'pending_approval' => ['label' => 'Pending Approval', 'icon' => 'bi-clock', 'color' => 'warning'],
            'approved' => ['label' => 'Approved', 'icon' => 'bi-check-circle', 'color' => 'success'],
            'rejected' => ['label' => 'Rejected', 'icon' => 'bi-x-circle', 'color' => 'danger'],
            'revision_requested' => ['label' => 'Revision Requested', 'icon' => 'bi-arrow-repeat', 'color' => 'info'],
            'resubmitted' => ['label' => 'Resubmitted', 'icon' => 'bi-arrow-up-circle', 'color' => 'primary'],
            'data_updated' => ['label' => 'Data Updated', 'icon' => 'bi-pencil', 'color' => 'secondary'],
        ];

        return $labels[$this->action] ?? ['label' => $this->action, 'icon' => 'bi-circle', 'color' => 'secondary'];
    }

    /**
     * Get formatted action by
     */
    public function getFormattedActionByAttribute()
    {
        if ($this->action_by_name) {
            return $this->action_by_name;
        }

        if ($this->action_by_type === self::TYPE_VENDOR) {
            return 'Vendor';
        }

        return 'System';
    }

    /**
     * Get time ago
     */
    public function getTimeAgoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Create history entry helper
     */
    public static function createEntry($vendorId, $action, $actionByType, $actionById, $actionByName, $notes = null, $dataSnapshot = null, $changedFields = null)
    {
        return self::create([
            'vendor_id' => $vendorId,
            'action' => $action,
            'action_by_type' => $actionByType,
            'action_by_id' => $actionById,
            'action_by_name' => $actionByName,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'data_snapshot' => $dataSnapshot,
            'changed_fields' => $changedFields,
        ]);
    }

    /**
     * Scope: By vendor
     */
    public function scopeForVendor($query, $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Scope: By action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Latest first
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope: Oldest first
     */
    public function scopeOldestFirst($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}
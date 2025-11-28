<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class VendorDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_documents';

    protected $fillable = [
        'vendor_id',
        'document_type',
        'document_path',
        'original_name',
        'file_size',
        'mime_type',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
        'valid_from',
        'valid_to',
        'is_expired',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'verified_at' => 'datetime',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_expired' => 'boolean',
    ];

    /**
     * Document type constants
     */
    const TYPE_PAN_CARD = 'PAN Card';
    const TYPE_GST_CERTIFICATE = 'GST Certificate';
    const TYPE_INCORPORATION = 'Certificate of Incorporation';
    const TYPE_MOA_AOA = 'MOA/AOA/Partnership Deed';
    const TYPE_MSME = 'MSME Certificate';
    const TYPE_OTHER = 'Other Document';

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

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
     * Get document URL
     */
    public function getDocumentUrlAttribute()
    {
        return $this->document_path 
            ? Storage::disk('public')->url($this->document_path) 
            : null;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return null;

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Check if document is expired
     */
    public function checkExpiry()
    {
        if (!$this->valid_to) return false;

        $isExpired = $this->valid_to->isPast();
        
        if ($isExpired !== $this->is_expired) {
            $this->update(['is_expired' => $isExpired]);
        }

        return $isExpired;
    }

    /**
     * Approve document
     */
    public function approve($userId)
    {
        return $this->update([
            'status' => self::STATUS_APPROVED,
            'verified_at' => now(),
            'verified_by' => $userId,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Reject document
     */
    public function reject($userId, $reason)
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'verified_at' => now(),
            'verified_by' => $userId,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Scope: By document type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope: Pending documents
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Approved documents
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: Rejected documents
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope: Expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('is_expired', true);
    }

    /**
     * Scope: Expiring soon (within 30 days)
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('valid_to')
                     ->where('valid_to', '<=', now()->addDays($days))
                     ->where('valid_to', '>', now());
    }
}
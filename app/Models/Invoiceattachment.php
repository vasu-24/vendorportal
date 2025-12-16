<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InvoiceAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'attachment_type',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'description',
    ];

    // =====================================================
    // CONSTANTS
    // =====================================================

    const TYPE_INVOICE = 'invoice';
    const TYPE_TRAVEL_DOCUMENT = 'travel_document';
    const TYPE_SUPPORTING = 'supporting';

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the invoice that owns this attachment
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeInvoiceType($query)
    {
        return $query->where('attachment_type', self::TYPE_INVOICE);
    }

    public function scopeTravelDocument($query)
    {
        return $query->where('attachment_type', self::TYPE_TRAVEL_DOCUMENT);
    }

    public function scopeSupporting($query)
    {
        return $query->where('attachment_type', self::TYPE_SUPPORTING);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Get full URL to the file
     */
    public function getFileUrlAttribute()
    {
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Check if file is an image
     */
    public function isImage()
    {
        return in_array($this->file_type, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Check if file is PDF
     */
    public function isPdf()
    {
        return $this->file_type === 'pdf';
    }

    /**
     * Get attachment type label
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'invoice' => 'Invoice Document',
            'travel_document' => 'Travel Document',
            'supporting' => 'Supporting Document',
        ];

        return $labels[$this->attachment_type] ?? 'Document';
    }

    /**
     * Delete file from storage
     */
    public function deleteFile()
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
    }
}
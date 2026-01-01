<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TravelInvoiceBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_invoice_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function invoice()
    {
        return $this->belongsTo(TravelInvoice::class, 'travel_invoice_id');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }

    public function getFileExtensionAttribute()
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function getIsImageAttribute()
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return in_array(strtolower($this->file_extension), $imageExtensions);
    }

    public function getIsPdfAttribute()
    {
        return strtolower($this->file_extension) === 'pdf';
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if file exists in storage
     */
    public function fileExists()
    {
        return Storage::disk('public')->exists($this->file_path);
    }

    /**
     * Delete file from storage
     */
    public function deleteFile()
    {
        if ($this->fileExists()) {
            Storage::disk('public')->delete($this->file_path);
        }
    }
}
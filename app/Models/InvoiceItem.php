<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'contract_item_id',
        'category_id',
        'particulars',
        'sac',
        'quantity',
        'unit',
        'rate',
        'tax_percent',
        'amount',
        'tag_id',
'tag_name',
'rm_approved',
'rm_approved_by',
'rm_approved_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the invoice this item belongs to
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the contract item (config) this is linked to
     */
    public function contractItem()
    {
        return $this->belongsTo(ContractItem::class);
    }

    /**
     * Get the category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Format amount with currency
     */
    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Format rate with currency
     */
    public function getFormattedRateAttribute()
    {
        return '₹' . number_format($this->rate, 2);
    }

    /**
     * Get quantity with unit
     */
    public function getQuantityWithUnitAttribute()
    {
        return $this->quantity . ' ' . ($this->unit ?? '');
    }
}

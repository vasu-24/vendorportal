<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'category_id',
        'description',
        'quantity',
        'unit',
        'rate',
        'amount',
        'invoiced_quantity',
        'invoiced_amount',
         'tag_id',      // Add this
    'tag_name',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'invoiced_quantity' => 'decimal:2',
        'invoiced_amount' => 'decimal:2',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the contract
     */
    public function contract()
    {
        return $this->belongsTo(Contract::class);
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
     * Calculate amount (quantity × rate)
     */
    public function calculateAmount()
    {
        $this->amount = $this->quantity * $this->rate;
        return $this->amount;
    }

    /**
     * Get remaining quantity
     */
    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->invoiced_quantity;
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmountAttribute()
    {
        return $this->amount - $this->invoiced_amount;
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Get formatted rate
     */
    public function getFormattedRateAttribute()
    {
        return '₹' . number_format($this->rate, 2);
    }

    /**
     * Get unit label
     */
    public function getUnitLabelAttribute()
    {
        $units = [
            'hrs' => 'Hours',
            'days' => 'Days',
            'months' => 'Months',
            'nos' => 'Numbers',
            'lot' => 'Lot',
        ];

        return $units[$this->unit] ?? $this->unit;
    }

    /**
     * Update invoiced amounts
     */
    public function addInvoicedAmount($quantity, $amount)
    {
        $this->invoiced_quantity += $quantity;
        $this->invoiced_amount += $amount;
        $this->save();
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_invoice_id',
        'mode',
        'mode_other',
        'particulars',
        'expense_date',
        'basic',
        'taxes',
        'service_charge',
        'gst',
        'gross_amount',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'basic' => 'decimal:2',
        'taxes' => 'decimal:2',
        'service_charge' => 'decimal:2',
        'gst' => 'decimal:2',
        'gross_amount' => 'decimal:2',
    ];

    // =====================================================
    // CONSTANTS
    // =====================================================
    
    const MODE_FLIGHT = 'flight';
    const MODE_CABS = 'cabs';
    const MODE_TRAIN = 'train';
    const MODE_INSURANCE = 'insurance';
    const MODE_ACCOMMODATION = 'accommodation';
    const MODE_VISA = 'visa';
    const MODE_OTHER = 'other';

    public static $modes = [
        'flight' => 'Flight',
        'cabs' => 'Cabs',
        'train' => 'Train',
        'insurance' => 'Insurance',
        'accommodation' => 'Accommodation',
        'visa' => 'Visa',
        'other' => 'Other',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function invoice()
    {
        return $this->belongsTo(TravelInvoice::class, 'travel_invoice_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeOfMode($query, $mode)
    {
        return $query->where('mode', $mode);
    }

    public function scopeFlight($query)
    {
        return $query->where('mode', self::MODE_FLIGHT);
    }

    public function scopeCabs($query)
    {
        return $query->where('mode', self::MODE_CABS);
    }

    public function scopeTrain($query)
    {
        return $query->where('mode', self::MODE_TRAIN);
    }

    public function scopeAccommodation($query)
    {
        return $query->where('mode', self::MODE_ACCOMMODATION);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Calculate gross amount
     */
    public function calculateGrossAmount()
    {
        $this->gross_amount = $this->basic + $this->taxes + $this->service_charge + $this->gst;
        return $this;
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getModeLabelAttribute()
    {
        if ($this->mode === 'other' && $this->mode_other) {
            return $this->mode_other;
        }
        
        return self::$modes[$this->mode] ?? 'Unknown';
    }

    public function getFormattedBasicAttribute()
    {
        return '₹' . number_format($this->basic, 2);
    }

    public function getFormattedTaxesAttribute()
    {
        return '₹' . number_format($this->taxes, 2);
    }

    public function getFormattedServiceChargeAttribute()
    {
        return '₹' . number_format($this->service_charge, 2);
    }

    public function getFormattedGstAttribute()
    {
        return '₹' . number_format($this->gst, 2);
    }

    public function getFormattedGrossAmountAttribute()
    {
        return '₹' . number_format($this->gross_amount, 2);
    }
}
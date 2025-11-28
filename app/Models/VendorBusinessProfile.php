<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorBusinessProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vendor_business_profiles';

    protected $fillable = [
        'vendor_id',
        'core_activities',
        'employee_count',
        'credit_period',
        'turnover_fy1',
        'turnover_fy2',
        'turnover_fy3',
        'industry_type',
        'business_category',
        'years_in_business',
        'major_clients',
        'certifications',
    ];

    protected $casts = [
        'years_in_business' => 'integer',
    ];

    /**
     * Relationship: Vendor
     */
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * Get average turnover
     */
    public function getAverageTurnoverAttribute()
    {
        $turnovers = array_filter([
            $this->turnover_fy1,
            $this->turnover_fy2,
            $this->turnover_fy3,
        ]);

        if (empty($turnovers)) return null;

        // Remove non-numeric characters and calculate average
        $numericTurnovers = array_map(function ($t) {
            return (float) preg_replace('/[^0-9.]/', '', $t);
        }, $turnovers);

        return array_sum($numericTurnovers) / count($numericTurnovers);
    }

    /**
     * Scope: By employee count
     */
    public function scopeByEmployeeCount($query, $count)
    {
        return $query->where('employee_count', $count);
    }

    /**
     * Scope: By industry type
     */
    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry_type', $industry);
    }

    /**
     * Scope: Large companies (500+ employees)
     */
    public function scopeLargeCompanies($query)
    {
        return $query->whereIn('employee_count', ['501-1000', '1000+']);
    }
}
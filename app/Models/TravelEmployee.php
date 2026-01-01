<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelEmployee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_name',
        'employee_code',
        'designation',
        'department',
        'tag_id',
        'tag_name',
        'email',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get invoices for this employee
     */
    public function invoices()
    {
        return $this->hasMany(TravelInvoice::class, 'employee_id');
    }

    /**
     * Get the manager assigned to this employee's tag
     */
    public function getManager()
    {
        $managerTag = ManagerTag::where('tag_id', $this->tag_id)->first();
        
        if ($managerTag) {
            return User::find($managerTag->user_id);
        }
        
        return null;
    }

    /**
     * Get the manager ID for this employee's tag
     */
    public function getManagerId()
    {
        $managerTag = ManagerTag::where('tag_id', $this->tag_id)->first();
        return $managerTag ? $managerTag->user_id : null;
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeOfTag($query, $tagId)
    {
        return $query->where('tag_id', $tagId);
    }

    public function scopeOfProject($query, $tagId)
    {
        return $query->where('tag_id', $tagId);
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    public function getFullNameAttribute()
    {
        if ($this->employee_code) {
            return $this->employee_name . ' (' . $this->employee_code . ')';
        }
        return $this->employee_name;
    }

    public function getProjectCodeAttribute()
    {
        return $this->tag_id;
    }

    public function getProjectNameAttribute()
    {
        return $this->tag_name;
    }

    public function getManagerNameAttribute()
    {
        $manager = $this->getManager();
        return $manager ? $manager->name : 'Unassigned';
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if employee has any invoices
     */
    public function hasInvoices()
    {
        return $this->invoices()->count() > 0;
    }

    /**
     * Get total invoice amount for this employee
     */
    public function getTotalInvoiceAmount()
    {
        return $this->invoices()->sum('gross_amount');
    }
}
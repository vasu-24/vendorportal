<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\ManagerTag; 

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relationship with Role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Check if user has specific permission
    public function hasPermission($slug)
    {
        if (!$this->role) {
            return false;
        }
        return $this->role->hasPermission($slug);
    }

    // Check if user is active
    public function isActive()
    {
        return $this->status === 'active';
    }

    // Check if user is Super Admin
    public function isSuperAdmin()
    {
        return $this->role && $this->role->slug === 'super-admin';
    }
    
    
    public function managerTags()
{
    return $this->hasMany(ManagerTag::class);
}




}
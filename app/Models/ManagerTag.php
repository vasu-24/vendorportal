<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerTag extends Model
{
    protected $fillable = [
        'user_id',
        'tag_id',
        'tag_name',
    ];

    // Relationship: Manager (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    // If you're on Laravel 9+, Model is already HasFactory in stub; leaving simple here is fine.
  protected $fillable = [
    'company_name',
    'short_name',  // ADD THIS
    'cin',
    'address',
    'logo',        // ADD THIS
];
}

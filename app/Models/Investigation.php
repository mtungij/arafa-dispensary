<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investigation extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'category',   // ← REQUIRED
        'price',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function requests()
    {
        return $this->hasMany(InvestigationRequest::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Clean Filtering)
    |--------------------------------------------------------------------------
    */

    public function scopeMinor($query)
    {
        return $query->where('category', 'minor');
    }

    public function scopeMajor($query)
    {
        return $query->where('category', 'major');
    }
}

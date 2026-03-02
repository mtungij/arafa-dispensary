<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'email',
        'phone',
        'comp_logo',
    ];

    protected $appends = ['logo_url']; // optional but clean

    public function users()
    {
        return $this->hasMany(User::class);
    }

  public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
    

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

      public function getLogoUrlAttribute(): string
    {
        return $this->comp_logo && file_exists(public_path('storage/'.$this->comp_logo))
            ? asset('storage/'.$this->comp_logo)
            : asset('images/default-logo.png');
    }
}
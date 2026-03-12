<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemRoute extends Model
{

    protected $fillable = [
        'name',
        'label',
    ];

    /**
     * Users that can access this route
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_routes');
    }
}

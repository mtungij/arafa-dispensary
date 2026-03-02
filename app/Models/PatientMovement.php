<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientMovement extends Model
{
    


  protected $fillable = [
        'visit_id',
        'from_department',
        'to_department',
        'moved_at',
    ];

       public function visit()
    {
        return $this->belongsTo(Visit::class);
    }
}

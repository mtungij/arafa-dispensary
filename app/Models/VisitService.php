<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitService extends Model
{
    protected $fillable = [
        'visit_id',
        'service_id',
        'price',
        'status'
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

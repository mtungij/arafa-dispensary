<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestigationRequest extends Model
{
    protected $fillable = [
        'visit_id',
        'investigation_id',
        'price',
        'status',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function investigation()
    {
        return $this->belongsTo(Investigation::class);
    }
}
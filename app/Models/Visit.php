<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visit extends Model
{
     protected $fillable = [
        'company_id',
        'patient_id',
        'doctor_id',
        'visit_type',
        'status',
        'current_department',
        'created_by',
    ];

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function patient() {
        return $this->belongsTo(Patient::class);
    }

    public function doctor() {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function invoice() {
        return $this->hasOne(Invoice::class);
    }

    public function movements()
{
    return $this->hasMany(PatientMovement::class);
}

public function investigationRequests()
{
    return $this->hasMany(InvestigationRequest::class);
}
public function invoices(): HasMany
{
    return $this->hasMany(Invoice::class);
}
}

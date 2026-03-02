<?php

namespace App\Exports;

use App\Models\PatientMovement;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MovementsExport implements FromCollection, WithHeadings
{
    protected $search;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($search, $dateFrom, $dateTo)
    {
        $this->search = $search;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {
        return PatientMovement::with('visit.patient', 'visit.invoice')
            ->whereHas('visit', function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->whereHas('visit.patient', function ($q) {
                if ($this->search) {
                    $q->where(function ($sub) {
                        $sub->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%')
                            ->orWhere('patient_number', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%');
                    });
                }
            })
            ->when($this->dateFrom, fn($q) => $q->whereDate('moved_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('moved_at', '<=', $this->dateTo))
            ->get()
            ->map(function ($movement) {

                $invoice = $movement->visit->invoice ?? null;

                return [
                    'Patient' => $movement->visit->patient->first_name . ' ' .
                                 $movement->visit->patient->last_name,
                    'Type' => optional($invoice)->patient_amount > 0 ? 'Cash' : 'Insurance',
                    'Amount' => optional($invoice)->total ?? 0,
                    'From' => $movement->from_department,
                    'To' => $movement->to_department,
                    'Date' => $movement->moved_at,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Patient',
            'Type',
            'Amount',
            'From',
            'To',
            'Date'
        ];
    }
}
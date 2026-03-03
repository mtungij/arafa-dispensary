<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoicesExport implements FromCollection, WithHeadings
{
    protected $search;
    protected $dateFrom;
    protected $dateTo;
    protected $companyId;

    public function __construct($search, $dateFrom, $dateTo, $companyId)
    {
        $this->search = $search;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->companyId = $companyId;
    }

    public function collection()
    {
        return Invoice::with('visit.patient')
            ->where('company_id', $this->companyId)
            ->where('status', 'covered_by_insurance')
            ->when($this->search, fn($q) => $q->whereHas('visit.patient', fn($q2) =>
                $q2->where('first_name','like','%'.$this->search.'%')
                   ->orWhere('last_name','like','%'.$this->search.'%')
                   ->orWhere('patient_number','like','%'.$this->search.'%')
            ))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at','>=',$this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at','<=',$this->dateTo))
            ->latest()
            ->get()
            ->map(function($invoice){
                return [
                    'Patient' => $invoice->visit->patient->first_name.' '.$invoice->visit->patient->last_name,
                    'MRN' => $invoice->visit->patient->patient_number,
                    'Amount' => $invoice->insurance_amount,
                    'Date' => $invoice->created_at->format('d M Y'),
                ];
            });
    }

    public function headings(): array
    {
        return ['Patient', 'MRN', 'Amount', 'Date'];
    }
}
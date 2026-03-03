<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RegistrationFeesExport implements FromCollection, WithHeadings
{
    protected $invoices;

    public function __construct($invoices)
    {
        $this->invoices = $invoices;
    }

    public function collection()
    {
        return $this->invoices->map(function ($invoice) {
            return [
                'Patient Name' => $invoice->visit->patient->first_name . ' ' .
                                  $invoice->visit->patient->last_name,
                'Patient Number' => $invoice->visit->patient->patient_number,
                'Amount' => $invoice->patient_amount,
                'Status' => $invoice->status,
                'Paid At' => $invoice->paid_at,
                'Created At' => $invoice->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Patient Name',
            'Patient Number',
            'Amount',
            'Status',
            'Paid At',
            'Created At',
        ];
    }
}
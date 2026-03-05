<?php

namespace App\Exports;

use App\Models\Medicine;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MedicinesExport implements FromQuery, WithHeadings
{
    protected $category;
    protected $type;

    public function __construct($category = null, $type = null)
    {
        $this->category = $category;
        $this->type = $type;
    }

    public function query()
    {
        $query = Medicine::query()
            ->where('company_id', auth()->user()->company_id);

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        return $query->orderBy('name')->orderBy('type');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Type',
            'Category',
            'Quantity',
            'Buy Price',
            'Sell Price (Cash)',
            'Sell Price (Insurance)',
            'Expire Date',
            'Created At',
            'Updated At'
        ];
    }
}
<?php

namespace App\Exports;

use App\Models\Investigation;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; 

class InvestigationsExport implements FromCollection, WithHeadings
{
    public $category;

    // Pass the filter category to the constructor
    public function __construct($category = null)
    {
        $this->category = $category;
    }

    // This method returns the data collection for Excel
    public function collection()
    {
        $query = Investigation::where('company_id', Auth::user()->company_id);

        // Apply category filter if selected
        if ($this->category) {
            $query->where('category', $this->category);
        }

        return $query->get(['name', 'category', 'price']); // Select only the needed columns
    }

    // Optional: Add column headings
    public function headings(): array
    {
        return [
            'Name',
            'Category',
            'Price',
        ];
    }
}

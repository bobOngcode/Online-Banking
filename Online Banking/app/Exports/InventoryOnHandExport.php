<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class InventoryOnHandExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        return collect($this->data);    
    }

    public function headings(): array
    {
        return [
            'BRAND',
            'MODEL',
            'CATEGORY',
            'WAREHOUS CODE',
            'WAREHOUS NAME',
            'IN-STOCK',
            'COMMITTED',
            'ORDERED',
            'AVAILABLE'

        ];
    }
}

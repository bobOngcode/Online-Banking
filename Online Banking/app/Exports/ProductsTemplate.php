<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsTemplate implements FromCollection, WithHeadings
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
        return collect($this->data) ;
    }

    public function headings(): array
    {
        return [
            'BRAND',
            'MODEL',
            'CATEGORY',
            'SERIAL',
            'QUANTITY',
        ];
    }
}

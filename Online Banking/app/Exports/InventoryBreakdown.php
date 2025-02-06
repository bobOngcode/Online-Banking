<?php

namespace App\Exports;
use App\InventoryReconciliationBreakdown;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class InventoryBreakdown  extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements WithCustomValueBinder, FromCollection, WithHeadings 
{
    protected $inventory_recon_id;
    protected $report_type;

    public function __construct($params)
    {
        $this->inventory_recon_id = $params['inventory_recon_id'];
        $this->report_type = $params['report_type'];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $report_type = $this->report_type;
        $products = InventoryReconciliationBreakdown::select('brand', 'model', 'product_category', 'sap_serial', 'physical_serial')
                                                    ->where('inventory_recon_id', $this->inventory_recon_id)
                                                    ->where(function($query) use ($report_type) {
                                                        // if variable 'report_type' is equal to 'DISCREPANCY' then filter record only with discrepancy
                                                        if($report_type == 'DISCREPANCY')
                                                        {
                                                            $query->where('sap_serial', '=', '---')
                                                                  ->orWhere('physical_serial', '=', '---');
                                                        }
                                                    })
                                                    ->get();

        return $products;    
    }

    public function headings(): array
    {
        return [
            'BRAND',
            'MODEL',
            'CATEGORY',
            'SAP SERIAL',
            'BRANCH SERIAL',
        ];
    }
}

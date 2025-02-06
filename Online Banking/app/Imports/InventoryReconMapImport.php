<?php

namespace App\Imports;

use App\InventoryReconciliationMap;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Auth;

class InventoryReconMapImport implements ToModel
{   

    private $rows = 0;

    use Importable;  

    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {   
        // insert all rows except first row(header)
        if($this->rows > 0)
        {
            $serial = is_numeric(@$row[3]) && strlen(@$row[3]) < 19 && !strpos(@$row[3], 'E') ? (integer) @$row[3] : @$row[3]; // if serial is numeric then convert it to integer due to long serial details
            $data = [
                'inventory_recon_id' => $this->params['inventory_recon_id'],
                'user_id' => $this->params['user_id'],
                'inventory_type' => $this->params['inventory_type'],
                'brand' => @$row[0],
                'model' => @$row[1],
                'product_category' => @$row[2],
                'serial' => $serial, // if serial is numeric then convert it to integer due to long serial details
            ];

            // explode serials with slash('/') - will be used to breakdown/split into 2 or more rows
            $serials = explode('/', $serial);

            if(count($serials) > 1)
            {
                // breakdown/split into 2 or more rows
                foreach ($serials as $value) {
                    $data['serial'] = is_numeric($value) && strlen($value) < 19 && !strpos($value, 'E') ? (integer) $value : $value;
                    InventoryReconciliationMap::create($data);
                }
            }
            else
            {
                return new InventoryReconciliationMap($data);
            }

            // return new InventoryReconciliationMap([
            //     'inventory_recon_id' => $this->params['inventory_recon_id'],
            //     'user_id' => $this->params['user_id'],
            //     'inventory_type' => $this->params['inventory_type'],
            //     'brand' => @$row[0],
            //     'model' => @$row[1],
            //     'product_category' => @$row[2],
            //     'serial' => is_numeric(@$row[3]) ? (integer) @$row[3] : @$row[3], // if serial is numeric then convert it to integer due to long serial details
            //     'quantity' => 1,
            // ]);
  

        }

        ++$this->rows;
        
    }
}

            
    
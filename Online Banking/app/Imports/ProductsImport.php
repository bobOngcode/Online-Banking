<?php

namespace App\Imports;

use App\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Auth;

class ProductsImport implements ToModel
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
        // // insert all rows except first row(header)
        // if($this->rows > 0)
        // {
        //     return new Product([
        //         'user_id' => Auth::user()->id,
        //         'branch_id' => $this->params['branch_id'],
        //         'branch_id'
        //     ]);
        // }

        ++$this->rows;
        
    }
}

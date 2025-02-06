<?php

namespace App\Imports;

use App\EmployeeMasterData;
use Maatwebsite\Excel\Concerns\ToModel;

class EmployeeMasterDataImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new EmployeeMasterData([
            //
        ]);
    }
}

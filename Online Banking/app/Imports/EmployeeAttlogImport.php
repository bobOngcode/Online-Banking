<?php

namespace App\Imports;

use App\EmployeeAttlog;
use Maatwebsite\Excel\Concerns\ToModel;

class EmployeeAttlogImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new EmployeeAttlog([
            //
        ]);
    }
}

<?php

namespace App\Imports;

use App\EmployeePremiums;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;

class EmployeePremiumsImport implements ToModel
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
            return new EmployeePremiums([
                'file_upload_log_id' => $this->params['file_upload_log_id'],
                'branch_id' => $this->params['branch_id'],
                'last_name' => @$row[0],	
                'first_name' => @$row[1],
                'middle_name' => @$row[2],
                'dob' => @$row[3],
                'date_hired' => @$row[4],
                'or_number' => @$row[5],
                'sss_no' => @$row[6],
                'sss_ee' => @$row[7],
                'sss_er' => @$row[8],
                'sss_ec' => @$row[9],
                'sss_total' => @($row[7] + $row[8] + $row[9]),
                'philhealth_no' => @$row[10],
                'philhealth_ee' => @$row[11],
                'philhealth_er' => @$row[12],
                'philhealth_total' => @($row[11] + $row[12]),
                'pagibig_no' => @$row[13],
                'pagibig_ee' => @$row[14],
                'pagibig_er' => @$row[15],
                'pagibig_total' => @($row[14] + $row[15]),
            ]);
        }

        ++$this->rows;
    }
}

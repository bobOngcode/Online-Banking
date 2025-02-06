<?php

namespace App\Imports;

use App\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;

class EmployeesImport implements ToModel
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
            return new Employee([
                'file_upload_log_id' => $this->params['file_upload_log_id'],
                'branch_id' => $this->params['branch_id'],
                'employee_code' => @$row[0],
                'last_name' => @$row[1],
                'first_name' => @$row[2],
                'middle_name' => @$row[3],
                'dob' => @$row[4],
                'address' => @$row[5],
                'contact' => @$row[6],
                'email' => @$row[7],
                'class' => @$row[8],
                'rank' => @$row[9],
                'department' => @$row[10],
                'cost_center_code' => @$row[11],
                'job_description' => @$row[12],
                'date_employed' => @$row[13],
                'gender' => @$row[14],
                'civil_status' => @$row[15],
                'tax_status' => @$row[16],
                'tin_no' => @$row[17],
                'tax_branch_code' => @$row[18],
                'pagibig_no' => @$row[19],
                'philhealth_no' => @$row[20],
                'sss_no' => @$row[21],
                'time_schedule' => @$row[22],
                'restday' => @$row[23],
                'educ_attain' => @$row[24],
                'school_attended' => @$row[25],
                'course' => @$row[26],
            ]);
        }

        ++$this->rows;
        
    }
}

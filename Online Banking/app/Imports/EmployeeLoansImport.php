<?php

namespace App\Imports;

use App\EmployeeLoans;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;

class EmployeeLoansImport implements ToModel
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
            return new EmployeeLoans([
                'file_upload_log_id' => $this->params['file_upload_log_id'],
                'branch_id' => $this->params['branch_id'],
                'last_name' => @$row[0],	
                'first_name' => @$row[1],	
                'middle_name' => @$row[2],	
                'position' => @$row[3],	
                'loan_type' => @$row[4],	
                'description' => @$row[5],
                'ref_no' => @$row[6],
                'date_granted' => @$row[7],	
                'principal_loan' => @$row[8],	
                'terms' => @$row[9],	
                'period_from' => @$row[10],	
                'period_to' => @$row[11],	
                'monthly_amortization' => @$row[12],	
                'total_months_paid' => @$row[13],
                'or_number' => @$row[14],	
                'interest' => @($row[9] * $row[12]) - $row[8],	// (terms * monthly_amortization) -- principal_loan
                'total_loan' => @($row[9] * $row[12]), // (terms * monthly_amortization)
                'total_paid' => @($row[12] * $row[13]),	// (total_months_paid * monthly_amortization)
                'remaining_term' => @($row[9] - $row[13]),	// (terms - total_months_paid)
                'balance' => @($row[9] * $row[12]) - ($row[12] * $row[13]),	//(terms * monthly_amortization) - (total_months_paid * monthly_amortization)
                
            ]);
        }

        ++$this->rows;
    }
}

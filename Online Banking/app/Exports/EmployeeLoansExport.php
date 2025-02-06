<?php

namespace App\Exports;

use App\EmployeeLoans;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class EmployeeLoansExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $file_upload_log_id;

    public function __construct($file_upload_log_id)
    {
        $this->file_upload_log_id = $file_upload_log_id;
    }

    public function collection()
    {
        $file_upload_log_id = $this->file_upload_log_id;

        $employee_loans = DB::table('employee_loans')
                      ->join('branches', 'employee_loans.branch_id', '=', 'branches.id')
                      ->select(DB::raw('branches.name as branch'),
                                'employee_loans.last_name',
                                'employee_loans.first_name',
                                'employee_loans.middle_name',
                                'employee_loans.position',
                                'employee_loans.loan_type',
                                'employee_loans.description',
                                'employee_loans.ref_no',
                                DB::raw("DATE_FORMAT(employee_loans.date_granted, '%m.%d.%Y') as date_granted"),
                                'employee_loans.principal_loan',
                                'employee_loans.interest',
                                'employee_loans.total_loan',
                                'employee_loans.terms',
                                DB::raw("DATE_FORMAT(employee_loans.period_from, '%m.%d.%Y') as period_from,
                                         DATE_FORMAT(employee_loans.period_to, '%m.%d.%Y') as period_to"),
                                'employee_loans.monthly_amortization',
                                'employee_loans.total_months_paid',
                                'employee_loans.total_paid',
                                'employee_loans.balance',
                                'employee_loans.remaining_term',
                                'employee_loans.or_number')
                    //   ->where(function($query) use ($branch_id){
                    //       if($branch_id <> 0)
                    //       {
                    //           $query->where('employee_loans.branch_id', '=', $branch_id);
                    //       }
                    //   })
                      ->where('employee_loans.file_upload_log_id','=', $file_upload_log_id)
                      ->get();
        return $employee_loans;
    }

    public function headings(): array
    {
        return [
            'BRANCH',
            'LAST NAME',	
            'FIRST NAME	',
            'MIDDLE NAME',    
            'POSITION',
            'LOAN TYPE',
            'DESCRIPTION',
            'REFERENCE NO.',
            'DATE GRANTED',
            'PRINCIPAL LOAN',
            'INTEREST',
            'TOTAL LOAN AMOUNT',
            'TERMS',
            'PERIOD FROM',
            'PERIOD TO',
            'MONTHLY AMORTIZATION',
            'TOTAL MONTHS PAID',
            'TOTAL AMOUNT PAID',
            'BALANCE AMOUNT',
            'REMAINING TERM',
            'ISSUED OR NUMBER'																		
        ];
    }

}

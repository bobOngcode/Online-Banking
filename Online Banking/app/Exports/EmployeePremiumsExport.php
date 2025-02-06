<?php

namespace App\Exports;

use App\EmployeePremiums;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class EmployeePremiumsExport implements FromCollection, WithHeadings
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

        $employee_premiums = DB::table('employee_premiums')
                      ->join('branches', 'employee_premiums.branch_id', '=', 'branches.id')
                      ->leftJoin('companies', 'branches.company_id', '=', 'companies.id')
                      ->select(DB::raw('branches.name as branch'),
                                'employee_premiums.last_name',
                                'employee_premiums.first_name',
                                'employee_premiums.middle_name',
                                DB::raw("DATE_FORMAT(employee_premiums.dob, '%m.%d.%Y') as dob,
                                DATE_FORMAT(employee_premiums.date_hired, '%m.%d.%Y') as date_hired"),
                                'employee_premiums.or_number',
                                'employee_premiums.sss_no',
                                'employee_premiums.sss_ee',
                                'employee_premiums.sss_er',
                                'employee_premiums.sss_ec',
                                'employee_premiums.sss_total',
                                'employee_premiums.philhealth_no',
                                'employee_premiums.philhealth_ee',
                                'employee_premiums.philhealth_er',
                                'employee_premiums.philhealth_total',
                                'employee_premiums.pagibig_no',
                                'employee_premiums.pagibig_ee',
                                'employee_premiums.pagibig_er',
                                'employee_premiums.pagibig_total')
                    //   ->where(function($query) use ($branch_id){
                    //       if($branch_id <> 0)
                    //       {
                    //           $query->where('employee_premiums.branch_id', '=', $branch_id);
                    //       }
                    //   })
                      ->where('employee_premiums.file_upload_log_id','=', $file_upload_log_id)
                      ->get();
        return $employee_premiums;
    }

    public function headings(): array
    {
        return [
            'BRANCH',
            'LAST NAME',	
            'FIRST NAME	',
            'MIDDLE NAME',    
            'BIRTH DATE',
            'DATE HIRED',
            'ISSUED OR NUMBER',
            'SSS NUMBER',
            'SSS EE',
            'SSS ER',
            'SSS EC',
            'SSS TOTAL',
            'PHILHEALTH NUMBER',
            'PHILHEALTH EE',
            'PHILHEALTH ER',
            'PHILHEALTH TOTAL',
            'PAGIBIG NUMBER',
            'PAGIBIG EE',
            'PAGIBIG ER',
            'PAGIBIG TOTAL'																
        ];
    }
}

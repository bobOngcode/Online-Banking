<?php

namespace App\Exports;

use App\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class EmployeesExport implements FromCollection, WithHeadings
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

        $employees = DB::table('employees')
                      ->join('branches', 'employees.branch_id', '=', 'branches.id')
                      ->leftJoin('companies', 'branches.company_id', '=', 'companies.id')
                      ->select(DB::raw('branches.name as branch, companies.name as company'),
                                'employees.employee_code',
                                'employees.last_name',
                                'employees.first_name',
                                'employees.middle_name',
                                DB::raw("DATE_FORMAT(employees.dob, '%m.%d.%Y') as dob"),
                                'employees.address',
                                'employees.contact',
                                'employees.email',
                                'employees.class',
                                'employees.rank',
                                'employees.department',
                                'employees.cost_center_code',
                                'employees.job_description',
                                DB::raw("DATE_FORMAT(employees.date_employed, '%m.%d.%Y') as date_employed"),
                                'employees.gender',
                                'employees.civil_status',
                                'employees.tax_status',
                                'employees.tin_no',
                                'employees.tax_branch_code',
                                'employees.pagibig_no',
                                'employees.philhealth_no',
                                'employees.sss_no',
                                'employees.time_schedule',
                                'employees.restday',
                                'employees.educ_attain',
                                'employees.school_attended',
                                'employees.course',
                                DB::raw("CONCAT(CAST((TIMESTAMPDIFF(DAY, employees.date_employed, date_format(NOW(),'%Y-%m-%d')) / 365) AS UNSIGNED), ' years(s) ',
                                        CAST(((TIMESTAMPDIFF(DAY, employees.date_employed, date_format(NOW(),'%Y-%m-%d')) % 365) / 30) AS UNSIGNED), ' month(s) ',
                                        ((TIMESTAMPDIFF(DAY, employees.date_employed, date_format(NOW(),'%Y-%m-%d')) % 365) % 30), ' day(s)')  as length_of_service"))
                    //   ->where(function($query) use ($branch_id){
                    //       if($branch_id <> 0)
                    //       {
                    //           $query->where('employees.branch_id', '=', $branch_id);
                    //       }
                    //   })
                      ->where('employees.file_upload_log_id','=', $file_upload_log_id)
                      ->get();
        return $employees;
    }

    public function headings(): array
    {
        return [
            'BRANCH',
            'COMPANY',	
            'EMPLOYEE CODE/ID',	
            'LAST NAME',	
            'FIRST NAME	',
            'MIDDLE NAME',    
            'DATE OF BIRTH (MM/DD/YR)',	   
            'CURRENT  ADDRESS',	
            'MOBILE NO.',	
            'EMAIL ADDRESS',	
            'EMPLOYEE CLASS',	
            'EMPLOYEE RANK',	
            'DEPARTMENT CODE',	
            'COST CENTER CODE',	
            'JOB DESCRIPTION',	
            'DATE EMPLOYED (MM/DD/YR)',	
            'GENDER',	
            'STATUS',	
            'TAX STATUS',	
            'TIN NO.',	
            'TAX BRANCH CODE',	
            'PAG-IBIG NO.',	
            'PHILHEALTH NO.',	
            'SSS NO.',	
            'TIME SCHEDULE',	
            'RESTDAY',
            'EDUCATIONAL ATTAINMENT',
            'SCHOOL ATTENDED',
            'COURSE',
            'LENGTH OF SERVICE'																				
        ];
    }
}

<?php

namespace App\Exports;

use App\EmployeeAttlog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class EmployeeAttlogExport implements FromCollection, WithHeadings
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

        $employee_attlogs = DB::table('employee_attlogs')
                              ->join('branches', 'employee_attlogs.branch_id', '=', 'branches.id')
                              ->select('branches.name as branch', 'employee_attlogs.user_id', 'employee_attlogs.date_time', 'employee_attlogs.device_id', 
                                       'employee_attlogs.status', 'employee_attlogs.verifying_type', 'employee_attlogs.work_code')
                              ->where('employee_attlogs.file_upload_log_id','=', $file_upload_log_id)
                            //   ->where(function($query) use ($branch_id){
                            //     if($branch_id <> 0)
                            //     {
                            //         $query->where('employee_attlogs.branch_id', '=', $branch_id);
                            //     }
                            //   })
                              ->get();

        return $employee_attlogs;
    }

    public function headings(): array
    {
        return [
            'BRANCH',
            'USER ID',	
            'DATE & TIME',
            'DEVICE ID',    
            'STATUS',
            'VERIFYING TYPE',
            'WORK CODE',																	
        ];
    }
}

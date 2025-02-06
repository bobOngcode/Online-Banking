<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Positions;
use App\EmployeeMasterData;
use App\RequiredEmployeeMap;
use App\Branch;
use App\Brand;
use DB;

class BranchManpowerReport implements FromCollection, WithHeadings
{
    protected $params;
    protected $positions = [
        'Branch Manager', 
        'Reserved Branch Manager',
        'Sales Supervisor', 
        'Reserved Sales Supervisor', 
        'Credit & Collection Supervisor', 
        'Reserved CCS',
        'Management System Supervisor',
        'Reserved MSS',
        'Sales Specialist',
        'Warehouseman',
        'Driver',
        'Helper',
        'Technician',
        'Account Analyst',
        'Cashier',
        'Bookkeeper - Finance',
        'Bookkeeper - Operations',
    ];

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function collection()
    { 

        $data = [];

        $asOf = $this->params['asOf'];
        $firstOfMonth = $this->params['firstOfMonth'];
        $asOfLastDayLastMonth = $this->params['asOfLastDayLastMonth'];
        $branch_id = $this->params['branch_id'];

        $branches = Branch::where(function($query)  use ($branch_id){
                                if($branch_id <> 0)
                                {
                                    $query->where('id', $branch_id);
                                }
                          })
                          ->get();

        foreach ($branches as $i => $branch) {
            $data[$i] = ['branch' => $branch->name];
            foreach ($this->positions as $j => $position) {
                $required_employee = RequiredEmployeeMap::with('position')
                                                        ->whereHas('position', function($query) use ($position) {
                                                            $query->where('name', $position);
                                                        })
                                                        ->where('branch_id', $branch->id)
                                                        ->first();

                $required = $required_employee ? $required_employee->quantity : null;

                $existing = EmployeeMasterData::whereDate('date_employed', '<=', $asOfLastDayLastMonth)
                                              
                                              ->where(function($query) use ($asOfLastDayLastMonth) {
                                                    $query->whereDate('date_resigned', '>', $asOfLastDayLastMonth)
                                                          ->orWhereIn('date_resigned', ['0000-00-00', null]);
                                              })
                                              ->whereHas('position', function($query) use ($position) {
                                                    $query->where('name', $position);
                                              })
                                              ->where('branch_id', $branch->id)
                                              ->get()
                                              ->count();

                $deployed = EmployeeMasterData::whereDate('date_employed', '>=', $firstOfMonth)
                                              ->whereDate('date_employed', '<=', $asOf)
                                              ->whereHas('position', function($query) use ($position) {
                                                $query->where('name', $position);
                                              })
                                              ->where('branch_id', $branch->id)
                                              ->get()
                                              ->count();

                $resigned = EmployeeMasterData::whereDate('date_resigned', '>=', $firstOfMonth)
                                              ->whereDate('date_resigned', '<=', $asOf)
                                              ->whereHas('position', function($query) use ($position) {
                                                $query->where('name', $position);
                                              })
                                              ->where('branch_id', $branch->id)
                                              ->get()
                                              ->count();

                $data[$i]['required.' . $position] = $required ? $required : 0;
                $data[$i]['deployed.' . $position] = $deployed ? $deployed : 0;
                $data[$i]['res/term.' . $position] = $resigned ? $resigned : 0;
                $data[$i]['existing.' . $position] = $existing ? $existing : 0;
                $current_employee = ($existing + $deployed) - $resigned;
                $data[$i]['vacant.' . $position] = $required - $current_employee;
                $data[$i]['ending.' . $position] = $current_employee;
            }
        }

        return collect($data);

    }

    public function headings(): array
    {   
        $column_names = ['Required', 'Deployed', 'Resigned/Terminated', 'Existing', 'Vacant', 'Ending'];
        $headings = ['Branch'];
        
        foreach ($this->positions as $position) {
            foreach ($column_names as $column) {
                $headings[] = $position . '.' .$column;
            }
        }

        return $headings;
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Employee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeKeyPerformance;
use Validator;
use Carbon\Carbon;

class EmployeeKeyPerformanceController extends Controller
{
    public function store(Request $request)
    {       

        $year = Carbon::now()->format('Y');

        $valid_fields = [
            'employee_id' => 'required|integer',
            'monthly_key_performances' => 'required',
            'monthly_key_performances.*.year' => 'required|integer|between:2020, '. $year,
            'monthly_key_performances.*.month' => 'required|in:January,February,March,April,May,June,July,August,September,October,November,December',
            'monthly_key_performances.*.grade' => 'numeric|between:0, 999999.99|nullable'
        ];

        $rules = [
            'employee_id.required' => 'Employee ID is required',
            'monthly_key_performances.required' => 'Monthly Key Performance List is required',
            'monthly_key_performances.*.employee_id.integer' => 'Employee ID must be an integer',
            'monthly_key_performances.*.year.required' => 'Year is required',
            'monthly_key_performances.*.year.integer' => 'Year must be an integer',
            'monthly_key_performances.*.year.between' => 'Year must be between 2020 and '. $year,
            'monthly_key_performances.*.month.required' => 'Month is required',
            'monthly_key_performances.*.month.in' => 'Invalid month',
            'monthly_key_performances.*.grade.numeric' => 'Invalid value',
            'monthly_key_performances.*.grade.between' => 'Invalid value',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $performances = $request->monthly_key_performances;
        $year = '';
        
        if(is_array($performances))
        {
            foreach ($performances as $key => $performance) {
                EmployeeKeyPerformance::create([
                    'employee_id' => $request->employee_id,
                    'year' => $performance['year'],
                    'month' => $performance['month'],
                    'grade' => $performance['grade'],
                ]);

                $year = $performance['year'];
            }
        }
        

        $performances = EmployeeKeyPerformance::where('employee_id', $request->employee_id)
                                              ->orderBy('year')
                                              ->orderBy('id')
                                              ->get();

        return response()->json(['success' => 'Record has been added', 'performances' => $performances], 200);
    }

    public function update(Request $request, $id)
    {       

        $year = Carbon::now()->format('Y');

        $valid_fields = [
            // 'employee_id' => 'required|integer',
            // 'year' => 'required|integer|between:2020, '. $year,
            // 'month' => 'required|in:JANUARY,FEBRUARY,MARCH,APRIL,MAY,JUNE,JULY,AUGUST,SEPTEMBER,OCTOBER,NOVEMBER,DECEMBER',
            'grade' => 'numeric|between:0, 999999.99|nullable'
        ];

        $rules = [
            // 'employee_id.required' => 'Employee ID is required',
            // 'employee_id.integer' => 'Employee ID must be an integer',
            // 'year.required' => 'Year is required',
            // 'year.integer' => 'Year must be an integer',
            // 'year.between' => 'Year must be between 2020 and '. $year,
            // 'month.required' => 'Month is required',
            // 'month.in' => 'Invalid month',
            'grade.numeric' => 'Invalid value',
            'grade.between' => 'Invalid value',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $performance = EmployeeKeyPerformance::find($id);
        // $performance->employee_id = $request->employee_id;
        // $performance->year = $request->year;
        // $performance->month = $request->month;
        $performance->grade = $request->grade;
        $performance->save();

        return response()->json(['success' => 'Record has been updated'], 200);
    }
    
    public function delete(Request $request)
    {
        // $performance_id = $request->performance_id;
        // $performance = EmployeeKeyPerformance::findOrFail($performance_id);
        
        // $performance->delete();

        $performances = EmployeeKeyPerformance::where('employee_id', $request->employee_id)
                                              ->where('year', $request->period);

        if(!count($performances->get()))
        {
            return response()->json(['error' => 'No record found'], 200);
        }

        $performances->delete();
        
        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeMasterData;
use App\Branch;
use App\Department;
use App\Position;
use App\RequiredEmployeeMap;
use App\EmployeeMasterDataFile;
use App\EmployeeKeyPerformance;
use App\Imports\EmployeeMasterDataImport;
use App\Exports\EmployeeMasterDataExport;
use App\Exports\BranchManpowerReport;
use Validator;
use Excel;
use DB;
use Carbon\Carbon;

class EmployeeMasterDataController extends Controller
{
    public function index()
    {
        $employees = $this->getEmployees()->get();
        $branches = Branch::with('company')->get();
        $positions = Position::with('rank')->get();
        $departments = Department::with('division')->get();

        return response()->json([
            'employees' => $employees,
            'branches' => $branches,
            'positions' => $positions,
            'departments' => $departments
        ], 200);
    }

    public function getEmployees() 
    {
        return EmployeeMasterData::with('branch')
        ->with('branch.company')
        ->with('department')
        ->with('department.division')
        ->with('position')
        ->with('position.rank')
        ->with('files')
        ->with(['key_performances' => function($query) {
            $query->orderBy('year')
                  ->orderBy('id');
        }])
        ->select(DB::raw("*,
                 FLOOR((TIMESTAMPDIFF(DAY, dob, date_format(NOW(),'%Y-%m-%d')) / 365)) as age,
                 CONCAT(FLOOR((TIMESTAMPDIFF(DAY, date_employed, date_format(IFNULL(date_resigned, NOW()),'%Y-%m-%d')) / 365)), ' years(s) ',
                 FLOOR(((TIMESTAMPDIFF(DAY, date_employed, date_format(IFNULL(date_resigned, NOW()),'%Y-%m-%d')) % 365) / 30)), ' month(s) ',
                 ((TIMESTAMPDIFF(DAY, date_employed, date_format(IFNULL(date_resigned, NOW()),'%Y-%m-%d')) % 365) % 30), ' day(s)')  as length_of_service
                 "
             )
        );
    }

    public function validator($data)
    {
        $rules = [
            'branch_id.required' => 'Branch is required',
            'branch_id.integer' => 'Branch must be an integer',
            'employee_code.required' => 'Employee Code is required',
            'last_name.required' => 'Lastname is required',
            'first_name.required' => 'Firstname is required',
            'birth_date.required' => 'Birth Date is required',
            'birth_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'address.required' => 'Address is required',
            'contact.required' => 'Contact is required',
            'email.emial' => 'Invalid Email',
            'position_id.required' => 'Rank is required',
            'department_id.required' => 'Department is required',
            'date_employed.required' => 'Date Employed is required',
            'date_employed.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'gender.required' => 'Gender is required',
            'gender.in' => 'Gender value must be "Female" or "Male"',
            'civil_status.required' => 'Civil Status is required',
            'civil_status.in' => 'Civil Status value must be Single, Married, Widowed or Legally Separated',
            'tin_no.required' => 'TIN No. is required',
            'pagibig_no.required' => 'Pag-IBIG No. is required',
            'philhealth_no.required' => 'PhilHealth No. is required',
            'sss_no.required' => 'SSS No. is required',
            'educ_attain.required' => 'Educational Attainment is required',
            'school_attended.required' => 'School Attended is required',
            'course.required' => 'Course is required',
            'employment_type.required' => 'Employment Type is required',
            'employment_type.in' => 'Civil Status value must be Probationary or Regular',
            'active' => 'Status is required',
        ];

        $valid_fields = [
            'branch_id' => 'required|integer',
            'employee_code' => 'required',
            'last_name' => 'required',
            'first_name' => 'required',
            'birth_date' => 'required|date_format:Y-m-d',
            'address' => 'required',
            'contact' => 'required',
            'email' => 'nullable|email',
            'position_id' => 'required',
            'department_id' => 'required',
            'date_employed' => 'required|date_format:Y-m-d',
            'gender' => 'required|in:Male,Female,MALE,FEMALE',
            'civil_status' => 'required|in:Single,Married,Widowed,Legally Separated,SINGLE,MARRIED,WIDOWED,LEGALLY SEPARATED',
            'tin_no' => 'required',
            'pagibig_no' => 'required',
            'philhealth_no' => 'required',
            'sss_no' => 'required',
            'educ_attain' => 'required',
            'school_attended' => 'required',
            'employment_type' => 'required|in:Probationary,Regular,Probationary,REGULAR',
            'course' => 'required',
            'active' => 'required|boolean',
        ];

        $validator = Validator::make($data, $valid_fields, $rules);

        return $validator;
    }

    public function save($employee, $data)
    {
        $employee->branch_id = $data->get('branch_id');
        $employee->employee_code = $data->get('employee_code');
        $employee->last_name = $data->get('last_name');
        $employee->first_name = $data->get('first_name');
        $employee->middle_name = $data->get('middle_name');
        $employee->dob = $data->get('birth_date');
        $employee->address = $data->get('address');
        $employee->contact = $data->get('contact');
        $employee->email = $data->get('email');
        $employee->position_id = $data->get('position_id');
        $employee->department_id = $data->get('department_id');
        $employee->date_employed = $data->get('date_employed');
        $employee->date_resigned = $data->get('date_resigned');
        $employee->gender = $data->get('gender');
        $employee->civil_status = $data->get('civil_status');
        $employee->tin_no = $data->get('tin_no');
        $employee->pagibig_no = $data->get('pagibig_no');
        $employee->philhealth_no = $data->get('philhealth_no');
        $employee->sss_no = $data->get('sss_no');
        $employee->educ_attain = $data->get('educ_attain');
        $employee->school_year = $data->get('school_year');
        $employee->school_attended = $data->get('school_attended');
        $employee->course = $data->get('course');
        $employee->employment_type = $data->get('employment_type');
        $employee->regularization_date = $data->get('regularization_date');
        $employee->last_day_of_work = $data->get('last_day_of_work');
        $employee->reason_of_resignation = $data->get('reason_of_resignation');
        $employee->coe_is_issued = $data->get('coe_is_issued');
        $employee->last_pay_is_issued = $data->get('last_pay_is_issued');
        $employee->compliance = $data->get('compliance');
        $employee->active = $data->get('active');
        $employee->save();

        return $employee;
    }

    public function store(Request $request)
    {       
        $validator = $this->validator($request->all());
        
        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $employee_files_errors = [];
        $employee_files = is_array($request->employee_files) ? $request->employee_files : [];
        $document_types = $request->document_types;
        
        // upload files
        foreach ($employee_files as $key => $file) {
            if($file)
            {
                $file_validator = $this->file_validator($file);

                if($file_validator->fails())
                {
                    $employee_files_errors[$key] =   $file_validator->errors();
                }
            }
        }

        if(count($employee_files_errors))
        {
            return response()->json(['employee_files_errors' => $employee_files_errors], 200);
        }

        $employee = new EmployeeMasterData();
        $employee = $this->save($employee, $request);

        foreach ($employee_files as $key => $file) {
            $document_type = $document_types[$key];
            $data = [
                'employee_id' => $employee->id,
                'file' => $file,
                'title' => $document_type,
            ];

            $this->file_save($data);
        }

        $performances = json_decode($request->monthly_key_performances);
        if(is_array($performances))
        {
            foreach ($performances as $key => $performance) {

                EmployeeKeyPerformance::create([
                    'employee_id' => $employee->id,
                    'year' => $performance->year,
                    'month' => $performance->month,
                    'grade' => $performance->grade,
                ]);
            }
        }
        
        $employee = $this->getEmployees()->find($employee->id);

        return response()->json(['success' => 'Record has been added', 'employee' => $employee], 200);
    }

    public function file_validator($file) 
    {   
    
        $file_extension = $file->getClientOriginalExtension();

        return Validator::make(
            [   
                'file_ext' => strtolower($file_extension),
                'file' => $file,
            ],
            [
                'file_ext' => 'in:jpeg,jpg,png,docs,docx,pdf',
                'file' => 'max: 5200'
            ], 
            [
                'file_ext.in' => 'File type must be jpeg, jpg, png, docs, docx or pdf.',
                'file.max' => 'File size maximum is 5MB.'
            ]
        );  
        
    }

    public function file_save ($data)
    {
        try {   
            $file = $data['file'];
            $validator = $this->file_validator($file);
            
            if($validator->fails())
            {
                return response()->json(['error' => $validator->errors()], 200);
            }  

            $file_extension = $file->getClientOriginalExtension();

            $file_date = Carbon::now()->format('Y-m-d');
            $file_name = time().$file->getClientOriginalName();
            $file_path = '/wysiwyg/employee_basic_requirements/' . $file_date;

            $file->move(public_path() . $file_path, $file_name);
            
            $employee_file = new EmployeeMasterDataFile();
            $employee_file->employee_id = $data['employee_id'];
            $employee_file->file_name = $file_name;
            $employee_file->file_path = $file_path;
            $employee_file->file_type = $file_extension;
            $employee_file->title = $data['title'];
            $employee_file->save();

            return response()->json(['success' => 'File has been uploaded', 'file' => $employee_file], 200);
                
        } catch (\Exception $e) {
        
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }

    public function file_upload(Request $request, $employee_id)
    {
        $data = [
            'employee_id' => $employee_id,
            'file' => $request->file,
            'title' => $request->document_type,
        ];

        return $this->file_save($data);
        
    }

    public function file_download(Request $request)
    {
        try {

            $file = EmployeeMasterDataFile::find($request->file_id);

            $title = $file->title; 
            $file_path = $file->file_path;    
            $file_name = $file->file_name;
            $file_type = $file->file_type;

            $exploded = explode('/', $title); //explode or split the file type e.g 'Diploma/Copy of grades'

            $title = count($exploded) ? join('_', $exploded) : $title;

            $file = public_path() . $file_path . "/" . $file_name;
            $headers = array('Content-Type: application/' . $file_type,);

            return response()->download($file, $title . '.' . $file_type, $headers);

        } catch (\Exception $e) {
                
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }

    public function update(Request $request, $employee_id)
    {
   
        $validator = $this->validator($request->all());

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        
        $employee_files_errors = [];
        $employee_files = is_array($request->employee_files) ? $request->employee_files : [];
        $document_types = $request->document_types;
        
        // upload files
        foreach ($employee_files as $key => $file) {
            if($file)
            {
                $file_validator = $this->file_validator($file);

                if($file_validator->fails())
                {
                    $employee_files_errors[$key] =   $file_validator->errors();
                }
            }
        }

        if(count($employee_files_errors))
        {
            return response()->json(['employee_files_errors' => $employee_files_errors], 200);
        }

        $employee = EmployeeMasterData::find($employee_id);
        $employee = $this->save($employee, $request);

        foreach ($employee_files as $key => $file) {
            $document_type = $document_types[$key];
            $data = [
                'employee_id' => $employee->id,
                'file' => $file,
                'title' => $document_type,
            ];

            $this->file_save($data);
        }

        $employee = $this->getEmployees()->find($employee->id);
  
        return response()->json(['success' => 'Record has been updated', 'employee' => $employee, $this->index()], 200);

    }

    public function delete(Request $request)
    {   
        $employee_id = $request->employee_id;
        $employee = EmployeeMasterData::findOrFail($employee_id);

        if(is_array($employee_id))
        {
            $employee = EmployeeMasterData::whereIn('id', $employee_id);
        }
        
        $employee->delete();

        $files = EmployeeMasterDataFile::where('employee_id', $employee_id)->get();
    
		foreach ($files as $file) {

			//if record is empty then display error page
			if(empty($file->id))
			{
				return abort(404, 'Not Found');
			}

			EmployeeMasterDataFile::find($file->id)->delete();

			$file_path = $file->file_path;

			$path = public_path() . $file_path . "/" . $file->file_name;
			unlink($path);
		}

        EmployeeKeyPerformance::where('employee_id', $employee_id)->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function file_delete(Request $request)
    {

        $file = EmployeeMasterDataFile::find($request->get('id'));
        
        //if record is empty then display error page
        if(empty($file->id))
        {
            return abort(404, 'Not Found');
        }

        $file->delete();

        $file_path = $file->file_path;

        $path = public_path() . $file_path . "/" . $file->file_name;
        unlink($path);

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function import(Request $request)
    {
        try {
    
            if ($request->file('file')) {
                    
                $collection = Excel::toCollection(new EmployeeMasterDataImport(), $request->file('file'))[0];
                $ctr_collection = count($collection);
                $columns = [
                    'employee_code',
                    'branch',
                    'last_name',
                    'first_name',
                    'middle_name',
                    'birth_date',
                    'address',
                    'contact',
                    'email',
                    'position',
                    'department',
                    'date_employed',
                    'date_resigned',
                    'gender',
                    'civil_status',
                    'tin_no',
                    'pagibig_no',
                    'philhealth_no',
                    'sss_no',
                    'educ_attain',
                    'school_year',
                    'school_attended',
                    'course',
                    'employment_type',
                    'regularization_date',
                    // 'last_day_of_work',
                    // 'reason_of_resignation',
                    // 'coe_is_issued',
                    // 'last_pay_is_issued',
                    // 'compliance',
                    'active',
                ]; 

                $collection_errors = [];
                $collection_column_errors = [];
                $fields = [];    

                // if no. of columns did not match
                if(count($collection[0]) <> count($columns))
                {
                    $collection_column_errors[] = 'Number of columns did not match';    
                    return response()->json(['error_column' => $collection_column_errors], 200);                                
                }
                elseif($ctr_collection > 1)
                {   
               
                    for($x=0; $ctr_collection > $x; $x++)
                    {   
                        for($y=0; count($collection[$x]) > $y; $y++)
                        {
                            if($x == 0)
                            {   
                                
                                // if column name did not match
                                if($collection[$x][$y] != $columns[$y])
                                {
                                    $collection_column_errors[] =  'Invalid column name "'. $collection[$x][$y] . '" on column index ' . $y . '. Column name must be "' . $columns[$y] . '"';
                                } 
                            }  
                            else
                            {   
                                $fields[$x - 1][$columns[$y]] = $collection[$x][$y];
                            }
                        }

                        // if columns has errors
                        if(count($collection_column_errors))
                        {
                            return response()->json(['error_column' => $collection_column_errors], 200);
                        }

                    } 

                    $rules = [
                        '*.branch.required' => 'Branch is required',
                        '*.branch.notexists' => 'Branch is not registered',
                        '*.employee_code.required' => 'Employee Code is required',
                        '*.last_name.required' => 'Lastname is required',
                        '*.first_name.required' => 'Firstname is required',
                        '*.birth_date.required' => 'Birth Date is required',
                        '*.birth_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.address.required' => 'Address is required',
                        '*.contact.required' => 'Contact is required',
                        '*.email.email' => 'Invalid Email format',
                        '*.position.required' => 'Rank is required',
                        '*.department.required' => 'Department is required',
                        '*.date_employed.required' => 'Date Employed is required',
                        '*.date_employed.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.date_resigned.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.gender.required' => 'Gender is required',
                        '*.gender.in' => 'Gender value must be "Female" or "Male"',
                        '*.civil_status.required' => 'Civil Status is required',
                        '*.civil_status.in' => 'Civil Status value must be Single, Married, Widowed or Legally Separated',
                        '*.tin_no.required' => 'TIN No. is required',
                        '*.pagibig_no.required' => 'Pag-IBIG No. is required',
                        '*.philhealth_no.required' => 'PhilHealth No. is required',
                        '*.sss_no.required' => 'SSS No. is required',
                        '*.educ_attain.required' => 'Educational Attainment is required',
                        '*.school_year.required' => 'School Year is required',
                        '*.school_attended.required' => 'School Attended is required',
                        '*.course.required' => 'Course is required',
                        '*.employment_type.required' => 'Employment Type is required',
                        '*.employment_type.in' => 'Employment Type value must be Probationary or Regular',
                        '*.regularization_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        // '*.last_day_of_work.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        // '*.coe_is_issued.in' => 'Issued COE field value must be 0 (Inactive) or 1 (Active)',
                        // '*.last_pay_is_issued.in' => 'Issued Last Pay field value must be 0 (Inactive) or 1 (Active)',
                        // '*.compliance.in' => 'Compliance value must be Render 30 Days, Render 60 Days or Non-Compliant',
                        '*.active.required' => 'Active field is required',
                        '*.active.in' => 'Active value must be 0 (Inactive) or 1 (Active)',
                    ];
            
                    $valid_fields = [
                        '*.branch' => 'required',
                        '*.employee_code' => 'required',
                        '*.last_name' => 'required',
                        '*.first_name' => 'required',
                        '*.birth_date' => 'required|date_format:Y-m-d',
                        '*.address' => 'required',
                        '*.contact' => 'required',
                        '*.email' => 'nullable|email',
                        '*.position' => 'required',
                        '*.department' => 'required',
                        '*.date_employed' => 'required|date_format:Y-m-d',
                        '*.date_resigned' => 'nullable|date_format:Y-m-d',
                        '*.gender' => 'required|in:Male,Female,MALE,FEMALE',
                        '*.civil_status' => 'required|in:Single,Married,Widowed,Legally Separated,SINGLE,MARRIED,WIDOWED,LEGALLY SEPARATED',
                        '*.tin_no' => 'required',
                        '*.pagibig_no' => 'required',
                        '*.philhealth_no' => 'required',
                        '*.sss_no' => 'required',
                        '*.educ_attain' => 'required',
                        '*.school_attended' => 'required',
                        '*.course' => 'required',
                        '*.employment_type' => 'required|in:Probationary,Regular',
                        '*.regularization_date' => 'nullable|date_format:Y-m-d',
                        // '*.last_day_of_work' => 'nullable|date_format:Y-m-d',
                        // '*.coe_is_issued' => 'nullable|boolean|in:0,1',
                        // '*.last_pay_is_issued' => 'nullable|boolean|in:0,1',
                        // '*.compliance' => 'nullable|in:Render 30 Days,Render 60 Days,Non-Compliant',
                        '*.active' => 'required|boolean|in:0,1',
                    ];
                    
                    $validator = Validator::make($fields, $valid_fields, $rules);  
            
                    if($validator->fails())
                    {
                        $collection_errors =  $validator->errors();
                    }

                    // validate value of branch, department and position
                    if(!count($collection_errors))
                    {
                        foreach ($fields as $key => $field) {
                            // return $field;
                            $branch_ctr = Branch::where('name', $field['branch'])->count();
                            $department_ctr = Department::where('name', $field['department'])->count();
                            $positon_ctr = Position::where('name', $field['position'])->count();
                            $msg = 'value is not registered';
    
                            if(!$branch_ctr)
                            {
                                $collection_errors[$key.'.branch'] = ['Branch ' . $msg];
                            }
    
                            if(!$department_ctr)
                            {
                                $collection_errors[$key.'.department'] = ['Department ' . $msg];
                            }
    
                            if(!$positon_ctr)
                            {
                                $collection_errors[$key.'.position'] = ['Position ' . $msg];
                            }
                        }
                    }

                }
                else
                {   
                    // if file has no row data
                    return response()->json(['error_empty' => 'File is Empty'], 200);
                }

                // if row data has errors
                if(count($collection_errors))
                {
                    return response()->json(['error_row_data' => $collection_errors, 'field_values' => $fields], 200);
                }
                else
                {   
                    // import excel file
                    // Excel::import(new EmployeeMasterDataImport(), $path);

                    foreach ($fields as $field) {

                        $branch_id = Branch::where('name', $field['branch'])->first()->id;
                        $department_id = Department::where('name', $field['department'])->first()->id;
                        $position_id = Position::where('name', $field['position'])->first()->id;

                        EmployeeMasterData::create([
                            'branch_id' => $branch_id,
                            'employee_code' => $field['employee_code'],
                            'last_name' => $field['last_name'],
                            'first_name' => $field['first_name'],
                            'middle_name' => $field['middle_name'],
                            'dob' => $field['birth_date'],
                            'address' => $field['address'],
                            'contact' => $field['contact'],
                            'email' => $field['email'],
                            'position_id' => $position_id,
                            'department_id' => $department_id,
                            'date_employed' => $field['date_employed'],
                            'date_resigned' => $field['date_resigned'],
                            'gender' => $field['gender'],
                            'civil_status' => $field['civil_status'],
                            'tin_no' => $field['tin_no'],
                            'pagibig_no' => $field['pagibig_no'],
                            'philhealth_no' => $field['philhealth_no'],
                            'sss_no' => $field['sss_no'],
                            'educ_attain' => $field['educ_attain'],
                            'school_year' => $field['school_year'],
                            'school_attended' => $field['school_attended'],
                            'course' => $field['course'],
                            'employment_type' => $field['employment_type'],
                            'regularization_date' => $field['regularization_date'],
                            'active' => $field['active']
                        ]);
                    }

                }
                    
                return response()->json([
                    'success' => 'Record has successfully imported', 
                    $fields
                ], 200);
            }
            else
            {
                return response()->json(['error_empty' => 'File is empty'], 200);
            }
          
        } catch (\Exception $e) {
          
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }

    public function export(Request $request)
    {
        $asOf = $request->asOfDate;
        $asOfLastDayLastMonth = Carbon::parse($asOf)->subMonthsNoOverflow()->endOfMonth()->toDateString();
        $firstOfMonth = Carbon::parse($asOf)->firstOfMonth();
        $branch_id = $request->branch_id;

        if($request->report_type == 'Employee List')
        {
            return Excel::download(new EmployeeMasterDataExport($request->all()), 'Employee List.xls');
        }
        else if($request->report_type == 'Branch Manpower Report')
        {   
            $params = [
                'branch_id' => $branch_id,
                'asOf' => $asOf,
                'asOfLastDayLastMonth' => $asOfLastDayLastMonth,
                'firstOfMonth' => $firstOfMonth
            ];

            return Excel::download(new BranchManpowerReport($params), 'Branch Manpower Report.xls');
        }
        
    }
    
}

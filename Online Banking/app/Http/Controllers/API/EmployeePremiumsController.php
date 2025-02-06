<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeePremiums;
use App\Branch;
use App\FileUploadLog;
use Carbon\Carbon;
use Validator;
use DB;
use Excel;
use Auth;
use App\Imports\EmployeePremiumsImport;
use App\Exports\EmployeePremiumsExport;

class EmployeePremiumsController extends Controller
{
    public function index()
    {
        
        $user_can_employee_premiums_list_all = Auth::user()->can('employee-premiums-list-all');

        $branches = Branch::with(['file_upload_logs' => function($query){
                                $query->select(DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_uploaded, DATE_FORMAT(docdate, '%m/%d/%Y') as docdate"))
                                    ->where('docname', '=', 'Employee Premiums')
                                    ->with('user');
                            }])
                            ->where(function($query) use ($user_can_employee_premiums_list_all){
                                // if user has no permission to view all the branches then select the user's branch only
                                if(!$user_can_employee_premiums_list_all)
                                {
                                    $query->where('id', '=', Auth::user()->branch_id);
                                }
                            })
                            ->get();

        return response()->json(['branches' => $branches], 200);
    }

    public function list_view(Request $request)
    {
        
        $file_upload_log_id = $request->get('file_upload_log_id');
        $file_upload_log = FileUploadLog::with('branch')
                                        ->select(DB::raw("id, branch_id, DATE_FORMAT(docdate, '%m/%d/%Y') as docdate, DATE_FORMAT(created_at, '%m/%d/%Y') as date_uploaded"))
                                        ->where('id', $file_upload_log_id)
                                        ->first();
        $employee_premiums = EmployeePremiums::with('branch')
                             ->select(DB::raw("*, DATE_FORMAT(employee_premiums.dob, '%m/%d/%Y') as birth_date, 
                                              DATE_FORMAT(employee_premiums.date_hired, '%m/%d/%Y') as date_hired"))
                             ->where('file_upload_log_id', '=', $file_upload_log_id)
                             ->get();  

        return response()->json(['employee_premiums' => $employee_premiums, 'file_upload_log' => $file_upload_log], 200);
    }

    public function create()
    {

    }

    public function store(Request $request)
    { 
      
        $rules = [
            'branch_id.required' => 'Branch is required',
            'branch_id.integer' => 'Branch must be an integer',
            'last_name.required' => 'Lastname is required',
            'first_name.required' => 'Firstname is required',
            'birth_date.required' => 'Birth Date is required',
            'birth_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'date_hired.required' => 'Date Hired is required',
            'date_hired.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'or_number.required' => 'OR Number is required',
            'sss_no.required' => 'SSS No. is required',
            'sss_ee.required' => 'Enter a valid value',
            'sss_ee.numeric' => 'Enter a valid value',
            'sss_ee.between' => 'Enter a valid value',
            'sss_er.required' => 'Enter a valid value',
            'sss_er.numeric' => 'Enter a valid value',
            'sss_er.between' => 'Enter a valid value',
            'sss_ec.required' => 'Enter a valid value',
            'sss_ec.numeric' => 'Enter a valid value',
            'sss_ec.between' => 'Enter a valid value',
            'sss_total.required' => 'Enter a valid value',
            'sss_total.numeric' => 'Enter a valid value',
            'sss_total.between' => 'Enter a valid value',
            'philhealth_no.required' => 'PhilHealth No. is required',
            'philhealth_no.required' => 'SSS No. is required',
            'philhealth_ee.required' => 'Enter a valid value',
            'philhealth_ee.numeric' => 'Enter a valid value',
            'philhealth_ee.between' => 'Enter a valid value',
            'philhealth_er.required' => 'Enter a valid value',
            'philhealth_er.numeric' => 'Enter a valid value',
            'philhealth_er.between' => 'Enter a valid value',
            'philhealth_total.required' => 'Enter a valid value',
            'philhealth_total.numeric' => 'Enter a valid value',
            'philhealth_total.between' => 'Enter a valid value',
            'pagibig_no.required' => 'Pag-IBIG No. is required',
            'pagibig_ee.required' => 'Enter a valid value',
            'pagibig_ee.numeric' => 'Enter a valid value',
            'pagibig_ee.between' => 'Enter a valid value',
            'pagibig_er.required' => 'Enter a valid value',
            'pagibig_er.numeric' => 'Enter a valid value',
            'pagibig_er.between' => 'Enter a valid value',
            'pagibig_total.required' => 'Enter a valid value',
            'pagibig_total.numeric' => 'Enter a valid value',
            'pagibig_total.between' => 'Enter a valid value',
        ];

        $valid_fields = [
            'branch_id' => 'required|integer',
            'last_name' => 'required',
            'first_name' => 'required',
            'birth_date' => 'required|date_format:Y-m-d',
            'date_hired' => 'required|date_format:Y-m-d',
            'sss_no' => 'required',
            'sss_ee' => 'required|numeric|between:1, 999999.99',
            'sss_er' => 'required|numeric|between:1, 999999.99',
            'sss_ec' => 'required|numeric|between:1, 999999.99',
            'sss_total' => 'required|numeric|between:1, 999999.99',
            'philhealth_no' => 'required',
            'philhealth_ee' => 'required|numeric|between:1, 999999.99',
            'philhealth_er' => 'required|numeric|between:1, 999999.99',
            'philhealth_total' => 'required|numeric|between:1, 999999.99',
            'pagibig_no' => 'required',
            'pagibig_ee' => 'required|numeric|between:1, 999999.99',
            'pagibig_er' => 'required|numeric|between:1, 999999.99',
            'pagibig_total' => 'required|numeric|between:1, 999999.99',
        ];
        
        $validator = Validator::make($request->all(), $valid_fields, $rules);  

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $employee_premiums = new EmployeePremiums();
        $employee_premiums->branch_id = $request->get('branch_id');
        $employee_premiums->last_name = $request->get('last_name');
        $employee_premiums->first_name = $request->get('first_name');
        $employee_premiums->dob = $request->get('birth_date');
        $employee_premiums->date_hired = $request->get('date_hired');
        $employee_premiums->or_number = $request->get('or_number');
        $employee_premiums->sss_no = $request->get('sss_no');
        $employee_premiums->sss_ee = $request->get('sss_ee');
        $employee_premiums->sss_er = $request->get('sss_er');
        $employee_premiums->sss_ec = $request->get('sss_ec');
        $employee_premiums->sss_total = $request->get('sss_total');
        $employee_premiums->philhealth_no = $request->get('philhealth_no');
        $employee_premiums->philhealth_ee = $request->get('philhealth_ee');
        $employee_premiums->philhealth_er = $request->get('philhealth_er');
        $employee_premiums->philhealth_total = $request->get('philhealth_total');
        $employee_premiums->pagibig_no = $request->get('pagibig_no');
        $employee_premiums->pagibig_ee = $request->get('pagibig_ee');
        $employee_premiums->pagibig_er = $request->get('pagibig_er');
        $employee_premiums->pagibig_total = $request->get('pagibig_total');
        $employee_premiums->save();

        $branch = Branch::find($request->get('branch_id'));

        return response()->json([
            'success' => 'Record has successfully added', 
            'employee_premiums' => $employee_premiums, 
            'branch' => $branch
        ], 200);

    }

    public function edit($employee_premiums_id)
    {
        $employee_premiums = EmployeePremiums::find($employee_premiums_id);
       
        //if record is empty then display error page
        if(empty($employee_premiums->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json([
            'employee' => $employee_premiums,
        ], 200);

    }

    public function update(Request $request, $employee_premiums_id)
    {  

        $rules = [
            'last_name.required' => 'Lastname is required',
            'first_name.required' => 'Firstname is required',
            'birth_date.required' => 'Birth Date is required',
            'birth_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'date_hired.required' => 'Date Hired is required',
            'date_hired.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'or_number.required' => 'OR Number is required',
            'sss_no.required' => 'SSS No. is required',
            'sss_ee.required' => 'Enter a valid value',
            'sss_ee.numeric' => 'Enter a valid value',
            'sss_ee.between' => 'Enter a valid value',
            'sss_er.required' => 'Enter a valid value',
            'sss_er.numeric' => 'Enter a valid value',
            'sss_er.between' => 'Enter a valid value',
            'sss_ec.required' => 'Enter a valid value',
            'sss_ec.numeric' => 'Enter a valid value',
            'sss_ec.between' => 'Enter a valid value',
            'sss_total.required' => 'Enter a valid value',
            'sss_total.numeric' => 'Enter a valid value',
            'sss_total.between' => 'Enter a valid value',
            'philhealth_no.required' => 'PhilHealth No. is required',
            'philhealth_no.required' => 'SSS No. is required',
            'philhealth_ee.required' => 'Enter a valid value',
            'philhealth_ee.numeric' => 'Enter a valid value',
            'philhealth_ee.between' => 'Enter a valid value',
            'philhealth_er.required' => 'Enter a valid value',
            'philhealth_er.numeric' => 'Enter a valid value',
            'philhealth_er.between' => 'Enter a valid value',
            'philhealth_total.required' => 'Enter a valid value',
            'philhealth_total.numeric' => 'Enter a valid value',
            'philhealth_total.between' => 'Enter a valid value',
            'pagibig_no.required' => 'Pag-IBIG No. is required',
            'pagibig_ee.required' => 'Enter a valid value',
            'pagibig_ee.numeric' => 'Enter a valid value',
            'pagibig_ee.between' => 'Enter a valid value',
            'pagibig_er.required' => 'Enter a valid value',
            'pagibig_er.numeric' => 'Enter a valid value',
            'pagibig_er.between' => 'Enter a valid value',
            'pagibig_total.required' => 'Enter a valid value',
            'pagibig_total.numeric' => 'Enter a valid value',
            'pagibig_total.between' => 'Enter a valid value',
        ];

        $valid_fields = [
            'last_name' => 'required',
            'first_name' => 'required',
            'birth_date' => 'required|date_format:Y-m-d',
            'date_hired' => 'required|date_format:Y-m-d',
            'sss_no' => 'required',
            'sss_ee' => 'required|numeric|between:1, 999999.99',
            'sss_er' => 'required|numeric|between:1, 999999.99',
            'sss_ec' => 'required|numeric|between:1, 999999.99',
            'sss_total' => 'required|numeric|between:1, 999999.99',
            'philhealth_no' => 'required',
            'philhealth_ee' => 'required|numeric|between:1, 999999.99',
            'philhealth_er' => 'required|numeric|between:1, 999999.99',
            'philhealth_total' => 'required|numeric|between:1, 999999.99',
            'pagibig_no' => 'required',
            'pagibig_ee' => 'required|numeric|between:1, 999999.99',
            'pagibig_er' => 'required|numeric|between:1, 999999.99',
            'pagibig_total' => 'required|numeric|between:1, 999999.99',
        ];
        
        $validator = Validator::make($request->all(), $valid_fields, $rules);  

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $employee_premiums = EmployeePremiums::find($employee_premiums_id);
        $employee_premiums->last_name = $request->get('last_name');
        $employee_premiums->first_name = $request->get('first_name');
        $employee_premiums->dob = $request->get('birth_date');
        $employee_premiums->date_hired = $request->get('date_hired');
        $employee_premiums->or_number = $request->get('or_number');
        $employee_premiums->sss_no = $request->get('sss_no');
        $employee_premiums->sss_ee = $request->get('sss_ee');
        $employee_premiums->sss_er = $request->get('sss_er');
        $employee_premiums->sss_ec = $request->get('sss_ec');
        $employee_premiums->sss_total = $request->get('sss_total');
        $employee_premiums->philhealth_no = $request->get('philhealth_no');
        $employee_premiums->philhealth_ee = $request->get('philhealth_ee');
        $employee_premiums->philhealth_er = $request->get('philhealth_er');
        $employee_premiums->philhealth_total = $request->get('philhealth_total');
        $employee_premiums->pagibig_no = $request->get('pagibig_no');
        $employee_premiums->pagibig_ee = $request->get('pagibig_ee');
        $employee_premiums->pagibig_er = $request->get('pagibig_er');
        $employee_premiums->pagibig_total = $request->get('pagibig_total');
        $employee_premiums->save();

        $branch = Branch::find($employee_premiums->branch_id);

        return response()->json([
            'success' => 'Record has been updated', 
            'employee_premiums' => $employee_premiums,
            'branch' => $branch,
        ], 200);
    } 


    public function import_premiums(Request $request, $branch_id) 
    {   

        try {
            $file_extension = '';
            $path = '';
            if($request->file('file'))
            {   
                $path1 = $request->file('file')->store('temp'); 
                $path=storage_path('app').'/'.$path1;  
                // $path = $request->file('file')->getRealPath();
                $file_extension = $request->file('file')->getClientOriginalExtension();
            }

            $validator = Validator::make(
                [
                    'file' => strtolower($file_extension),
                ],
                [
                    'file' => 'required|in:xlsx,xls,ods,odsx',
                ], 
                [
                    'file.required' => 'File is required',
                    'file.in' => 'File type must be xlsx/xls/ods/odsx.',
                ]
            );  
            
            if($validator->fails())
            {
                return response()->json($validator->errors(), 200);
            }
    
            if ($request->file('file')) {
                
                $params = ['branch_id' => $branch_id, 'file_upload_log_id' => 0];
                $collection = Excel::toCollection(new EmployeePremiumsImport($params), $request->file('file'))[0];
                $ctr_collection = count($collection);
                $columns = [
                    'last_name',
                    'first_name',
                    'middle_name',
                    'birth_date',
                    'date_hired',
                    'or_number',
                    'sss_no',
                    'sss_ee',
                    'sss_er',
                    'sss_ec',
                    // 'sss_total',
                    'philhealth_no',
                    'philhealth_ee',
                    'philhealth_er',
                    // 'philhealth_total',
                    'pagibig_no',
                    'pagibig_ee',
                    'pagibig_er',
                    // 'pagibig_total',
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
                        '*.last_name.required' => 'Lastname is required',
                        '*.first_name.required' => 'Firstname is required',
                        '*.birth_date.required' => 'Birth Date is required',
                        '*.birth_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.date_hired.required' => 'Date Hired is required',
                        '*.date_hired.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.or_number.required' => 'OR Number is required',
                        '*.sss_no.required' => 'SSS No. is required',
                        '*.sss_ee.required' => 'Enter a valid value',
                        '*.sss_ee.numeric' => 'Enter a valid value',
                        '*.sss_ee.between' => 'Enter a valid value',
                        '*.sss_er.required' => 'Enter a valid value',
                        '*.sss_er.numeric' => 'Enter a valid value',
                        '*.sss_er.between' => 'Enter a valid value',
                        '*.sss_ec.required' => 'Enter a valid value',
                        '*.sss_ec.numeric' => 'Enter a valid value',
                        '*.sss_ec.between' => 'Enter a valid value',
                        // '*.sss_total.required' => 'Enter a valid value',
                        // '*.sss_total.numeric' => 'Enter a valid value',
                        // '*.sss_total.between' => 'Enter a valid value',
                        '*.philhealth_no.required' => 'PhilHealth No. is required',
                        '*.philhealth_no.required' => 'SSS No. is required',
                        '*.philhealth_ee.required' => 'Enter a valid value',
                        '*.philhealth_ee.numeric' => 'Enter a valid value',
                        '*.philhealth_ee.between' => 'Enter a valid value',
                        '*.philhealth_er.required' => 'Enter a valid value',
                        '*.philhealth_er.numeric' => 'Enter a valid value',
                        '*.philhealth_er.between' => 'Enter a valid value',
                        // '*.philhealth_total.required' => 'Enter a valid value',
                        // '*.philhealth_total.numeric' => 'Enter a valid value',
                        // '*.philhealth_total.between' => 'Enter a valid value',
                        '*.pagibig_no.required' => 'Pag-IBIG No. is required',
                        '*.pagibig_ee.required' => 'Enter a valid value',
                        '*.pagibig_ee.numeric' => 'Enter a valid value',
                        '*.pagibig_ee.between' => 'Enter a valid value',
                        '*.pagibig_er.required' => 'Enter a valid value',
                        '*.pagibig_er.numeric' => 'Enter a valid value',
                        '*.pagibig_er.between' => 'Enter a valid value',
                        // '*.pagibig_total.required' => 'Enter a valid value',
                        // '*.pagibig_total.numeric' => 'Enter a valid value',
                        // '*.pagibig_total.between' => 'Enter a valid value',
                        
                    ];
            
                    $valid_fields = [
                        '*.last_name' => 'required',
                        '*.first_name' => 'required',
                        '*.birth_date' => 'required|date_format:Y-m-d',
                        '*.date_hired' => 'required|date_format:Y-m-d',
                        '*.sss_no' => 'required',
                        '*.sss_ee' => 'required|numeric|between:1, 999999.99',
                        '*.sss_er' => 'required|numeric|between:1, 999999.99',
                        '*.sss_ec' => 'required|numeric|between:1, 999999.99',
                        // '*.sss_total' => 'required|numeric|between:1, 999999.99',
                        '*.philhealth_no' => 'required',
                        '*.philhealth_ee' => 'required|numeric|between:1, 999999.99',
                        '*.philhealth_er' => 'required|numeric|between:1, 999999.99',
                        // '*.philhealth_total' => 'required|numeric|between:1, 999999.99',
                        '*.pagibig_no' => 'required',
                        '*.pagibig_ee' => 'required|numeric|between:1, 999999.99',
                        '*.pagibig_er' => 'required|numeric|between:1, 999999.99',
                        // '*.pagibig_total' => 'required|numeric|between:1, 999999.99',
                    ];
                    
                    $validator = Validator::make($fields, $valid_fields, $rules);  
            
                    if($validator->fails())
                    {
                        $collection_errors =  $validator->errors();
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
                    // file upload logs
                    $file_upload_log = new FileUploadLog();
                    $file_upload_log->branch_id = $branch_id;
                    $file_upload_log->docdate = $request->get('docdate');
                    $file_upload_log->docname = "Employee Premiums";
                    $file_upload_log->user_id = Auth::user()->id;
                    $file_upload_log->save();

                    $params = ['branch_id' => $branch_id, 'file_upload_log_id' => $file_upload_log->id];

                    // import excel file
                    Excel::import(new EmployeePremiumsImport($params), $path);
                }
                    
                return response()->json([
                    'success' => 'Record has successfully imported', 
                    'file_upload_log_id' => $file_upload_log->id
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

    public function export_premiums(Request $request)
    {   
        return Excel::download(new EmployeePremiumsExport($request->file_upload_log_id), 'employee_premiums.xls');
    }

    public function delete(Request $request)
    {   
        
        $branch_id = $request->get('branch_id');

        $employee_premiums = DB::table('employee_premiums')
                        ->where(function($query) use ($branch_id){
                            if($branch_id <> 0)
                            {
                                $query->where('employee_premiums.branch_id', '=', $branch_id);
                            }
                        });
        
        if($request->get('clear_list'))
        {   
            
            $file_upload_log_id = $request->get('file_upload_log_id');
            
            $file_upload_log = FileUploadLog::find($file_upload_log_id);
           
            $employee_premiums = DB::table('employee_premiums')
                           ->where('file_upload_log_id', '=', $file_upload_log_id);
            
            if(empty($file_upload_log->id))
            {
                return abort(404, 'Not Found');
            }
                 
            $file_upload_log->delete();

            $employee_premiums->delete();

        }
        else
        {
            
            $employee_premiums = EmployeePremiums::find($request->get('employee_premiums_id'));

            //if record is empty then display error page
            if(empty($employee_premiums->id))
            {
                return abort(404, 'Not Found');
            }

            $employee_premiums->delete();
        }

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

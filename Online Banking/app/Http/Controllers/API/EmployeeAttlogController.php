<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeAttlog;
use App\EmployeeFile;
use App\Branch;
use App\FileUploadLog;
use Carbon\Carbon;
use Validator;
use DB;
use Excel;
use App\Imports\EmployeeAttlogImport;
use App\Exports\EmployeeAttlogExport;
use File;
use Auth;

class EmployeeAttlogController extends Controller
{
    public function index()
    {   
        $user_can_employee_attlog_list_all = Auth::user()->can('employee-attlog-list-all');
     
        $branches = Branch::with(['file_upload_logs' => function($query){
                                    $query->select(DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_uploaded, DATE_FORMAT(docdate, '%m/%d/%Y') as docdate"))
                                        ->where('docname', '=', 'Employee Attlog');
                                }])
                                ->where(function($query) use ($user_can_employee_attlog_list_all){
                                    // if user has no permission to view all the branches then select the user's branch only
                                    if(!$user_can_employee_attlog_list_all)
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
        $employee_attlogs = EmployeeAttlog::with('branch')
                                    ->select(DB::raw('*, employee_attlogs.user_id, DATE_FORMAT(employee_attlogs.date_time, "%m/%d/%Y %H:%i:00") as date_time,
                                            employee_attlogs.device_id, employee_attlogs.status, employee_attlogs.verifying_type, employee_attlogs.work_code'))
                                    ->where('file_upload_log_id', '=', $file_upload_log_id)
                                    ->get(); 

        return response()->json(['employee_attlogs' => $employee_attlogs, 'file_upload_log' => $file_upload_log], 200);
    }

    public function create()
    {

    }

    public function store(Request $request)
    { 
    
        return response()->json([], 200);

    }

    public function edit($employee_attlog_id)
    {
        $employee_attlogs = EmployeeAttlog::find($employee_attlog_id_id);
       
        //if record is empty then display error page
        if(empty($employee_attlogs->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json([
            'employee_attlogs' => $employee_attlogs,
        ], 200);

    }

    public function update(Request $request, $employee_attlog_id)
    {  
        return response()->json([], 200);
    } 


    public function import_attlog(Request $request, $branch_id) 
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
                    'file' => 'required|in:dat',
                ], 
                [
                    'file.required' => 'File is required',
                    'file.in' => 'File type must be dat.',
                ]
            );  
            
            if($validator->fails())
            {
                return response()->json($validator->errors(), 200);
            }
    
            if ($request->file('file')) {
                    
                
                $attlog_file = File::get($request->file('file')); 

                $attlogs_explode = explode(PHP_EOL, $attlog_file);
                $attlogs = array_diff($attlogs_explode, ['']); // remove empty rows from $attlogs_explode array
                $attlogArray = [];
                

                // file upload logs
                $file_upload_log = new FileUploadLog();
                $file_upload_log->branch_id = $branch_id;
                $file_upload_log->docdate = $request->get('docdate');
                $file_upload_log->docname = "Employee Attlog";
                $file_upload_log->user_id = Auth::user()->id;
                $file_upload_log->save();

                foreach($attlogs as $key => $item) {
                        
                    $arr = explode("\t", $item); // sub array
                    $attlog = array_diff($arr, ['']); // remove all index with empty values
                    $attlogArray[$key] = $attlog;
                    $user_id = $attlog[0];
                    $date_time = $attlog[1];
                    $device_id = $attlog[2];
                    $status = $attlog[3];
                    $verifying_type = $attlog[4];
                    $work_code = $attlog[5];

                    $attlog = new EmployeeAttlog();
                    $attlog->file_upload_log_id = $file_upload_log->id;
                    $attlog->branch_id = $branch_id;
                    $attlog->user_id =  $user_id;
                    $attlog->date_time = $date_time;
                    $attlog->device_id = $device_id;
                    $attlog->status = $status;
                    $attlog->verifying_type = $verifying_type;
                    $attlog->work_code = $work_code;
                    $attlog->save();
                    
                }

                $file_date = Carbon::now()->format('Y-m-d');
                $uploadedFile = $request->file('file');
                $file_name = time().$uploadedFile->getClientOriginalName();
                $file_path = '/wysiwyg/employee_files/' . $file_date;

                $uploadedFile->move(public_path() . $file_path, $file_name);
                
                $employee_file = new EmployeeFile();
                $employee_file->file_upload_log_id = $file_upload_log->id;
                $employee_file->branch_id = $branch_id;
                $employee_file->file_name = $file_name;
                $employee_file->file_path = $file_path;
                $employee_file->file_type = $file_extension;
                $employee_file->title = "Attlog";
                $employee_file->save();
                    
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

    public function export_attlog(Request $request)
    {   
        return Excel::download(new EmployeeAttlogExport($request->file_upload_log_id), 'employee_attlogs.xls');
    }

    public function delete(Request $request)
    {   
        
        $branch_id = $request->get('branch_id');

        if($request->get('clear_list'))
        {   
            $file_upload_log_id = $request->get('file_upload_log_id');
            
            $file_upload_log = FileUploadLog::find($file_upload_log_id);

            $employee_attlogs = DB::table('employee_attlogs')
                                  ->where('file_upload_log_id', '=', $file_upload_log_id);
            
            if(empty($file_upload_log->id))
            {
                return response()->json('No record found', 200);
            }
            else
            {
                $employee_attlogs->delete();
                $file_upload_log->delete();
            }

            $employee_file = DB::table('employee_files')->where('file_upload_log_id', '=', $file_upload_log_id)->get()->first();

            // delete all files of specific branch from directory
            // foreach ($data as $key => $employee_file) {
            //     $file_name = $employee_file->file_name;
            //     $file_path = $employee_file->file_path;
            //     $path = public_path() . $file_path . "/" . $file_name;
            //     unlink($path);
            // }  
            
            // delete file from directory
            $file_name = $employee_file->file_name;
            $file_path = $employee_file->file_path;
            $path = public_path() . $file_path . "/" . $file_name;
            unlink($path);
            
            $employee_file = DB::table('employee_files')->where('file_upload_log_id', '=', $file_upload_log_id)->delete();

        }
        else
        {
            
            $employee_attlogs = EmployeeAttlog::find($request->get('employee_attlog_id'));

            //if record is empty then display error page
            if(empty($employee_attlogs->id))
            {
                return abort(404, 'Not Found');
            }

            $employee_attlogs->delete();
        }

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function download(Request $request)
    {   
        try {

            $file = EmployeeFile::with('branch')
                                ->where('file_upload_log_id', '=', $request->file_upload_log_id)
                                ->get()->first();

            $title = $file->title . ' - ' . $file->branch->name . ' Branch'; 
            $file_path = $file->file_path;    
            $file_name = $file->file_name;
            $file_type = $file->file_type;

            $file = public_path() . $file_path . "/" . $file_name;
            $headers = array('Content-Type: application/' . $file_type,);

            return response()->download($file, $title . '.' . $file_type, $headers);

        } catch (\Exception $e) {
            
            return response()->json(['error' => $e->getMessage()], 200);
        }
        
    }
}

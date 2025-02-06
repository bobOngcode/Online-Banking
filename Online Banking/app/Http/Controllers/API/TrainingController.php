<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\TrainingFile;
use App\FileHasPermission;
use App\Position;
use Validator;
use DB;
use Carbon\Carbon;
use Auth;
use Response;

class TrainingController extends Controller
{   
    public function files() {
        $user = Auth::user();
        // $videos = TrainingFile::where('file_type', '=', 'mp4')->get();
        // $videos = DB::table('training_files')
        //             ->join('file_has_permissions', 'training_files.id', '=', 'file_has_permissions.training_file_id')
        //             ->where(function($query) use ($user) {

        //                 // if auth is not Administrator
        //                 if($user->id != 1)
        //                 {
        //                     $query->where('training_files.user_id', '=', $user->id)
        //                           ->orWhere('file_has_permissions.position_id', '=', $user->position_id);
        //                 }
                       
        //             })
        //             ->select(DB::raw('training_files.*'))
        //             ->get();
        $files = TrainingFile::with('file_has_permissions') 
                              ->where(function($query) use ($user) {

                                    // if auth is not Administrator
                                    if($user->id != 1)
                                    {
                                        $query->where('training_files.user_id', '=', $user->id)
                                            ->orWhereHas('file_has_permissions', function($query) use ($user) {

                                                // if auth is not Administrator
                                                if($user->id != 1)
                                                {
                                                    $query->where('position_id', '=', $user->position_id);
                                                }
                                    
                                            });
                                    }
           
                                })
                                ->select(DB::raw('training_files.*'))
                                ->get();

        return response()->json(['files' => $files], 200);
    }

    public function user_files() {
        $user = Auth::user();
 
        $files = TrainingFile::with('file_has_permissions') 
                              ->where(function($query) use ($user) {

                                    // if auth is not Administrator
                                    if($user->id != 1)
                                    {
                                        $query->where('training_files.user_id', '=', $user->id);    
                                    }
           
                                })
                                ->select(DB::raw('training_files.*'))
                                ->get();

        $positions = Position::all();
        return response()->json(['files' => $files, 'positions' => $positions], 200);
    }

    public function file_upload(Request $request)
    {   
 
        try {
            $file_extension = '';
            $path = '';
            if($request->file('file'))
            {   
                $path = $request->file('file')->getRealPath();
                $file_extension = $request->file('file')->getClientOriginalExtension();
            }

            $validator = Validator::make(
                [
                    'file' => strtolower($file_extension),
                    'title' => $request->get('title'),
                    'permitted_positions' => $request->get('permitted_positions'),
                ],
                [
                    // 'file' => 'required|in:mp4,',
                    'file' => 'required',
                    'title' => 'required',
                    'permitted_positions' => 'required',
                ], 
                [
                    'file.required' => 'File is required',
                    'file.in' => 'File type must be mp4.',
                    'title.required' => 'Title is required',
                    'permitted_positions.required' => 'Permitted Positions is required',
                ]
            );  
            
            if($validator->fails())
            {
                return response()->json($validator->errors(), 200);
            }
    
            if ($request->file('file')) {

            }
            else
            {
                return response()->json(['error_empty' => 'File is empty'], 200);
            }
          
        } catch (\Exception $e) {
          
            return response()->json(['error' => $e->getMessage()], 200);
        }

        $file_date = Carbon::now()->format('Y-m-d');
        $uploadedFile = $request->file('file');
        $file_name = time().$uploadedFile->getClientOriginalName();
        $file_path = '/wysiwyg/training_files/' . $file_date;

        $uploadedFile->move(public_path() . $file_path, $file_name);
        
        $training_video = new TrainingFile();
        $training_video->user_id = Auth::user()->id;
        $training_video->file_name = $file_name;
        $training_video->file_path = $file_path;
        $training_video->file_type = $file_extension;
        $training_video->title = $request->get('title');
        $training_video->description = $request->get('description');
        $training_video->save();

        $permitted_positions = explode(",", $request->get('permitted_positions'));

        foreach ($permitted_positions as $key => $value) {
            $file_has_permissions = new FileHasPermission();
            $file_has_permissions->position_id = $value;
            $file_has_permissions->training_file_id = $training_video->id;
            $file_has_permissions->save();
        }

        return response()->json([
            'success' => 'File successfully uploaded'
        ]);
    }

    public function update(Request $request, $training_file_id)
    {   
        $validator = Validator::make(
            [
                'title' => $request->get('title'),
                'permitted_positions' => $request->get('permitted_positions'),
            ],
            [
                'title' => 'required',
                'permitted_positions' => 'required',
            ], 
            [
                'title.required' => 'Title is required',
                'permitted_positions.required' => 'Permitted Positions is required',
            ]
        );  
        
        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        
        $training_video = TrainingFile::find($training_file_id);
        $training_video->title = $request->get('title');
        $training_video->description = $request->get('description');
        $training_video->save();

        $file_permissions = FileHasPermission::where('training_file_id', '=', $training_file_id)->delete();

        $permitted_positions = explode(",", $request->get('permitted_positions'));
        
        foreach ($permitted_positions as $key => $value) {
            $file_has_permissions = new FileHasPermission();
            $file_has_permissions->position_id = $value;
            $file_has_permissions->training_file_id = $training_video->id;
            $file_has_permissions->save();
        }   

        $video_details = TrainingFile::with('file_has_permissions')->where('id', '=', $training_file_id)->get()->first();

        return response()->json([
            'success' => 'File successfully uploaded',
            'video_details' => $video_details,
        ]);
    }

    public function delete(Request $request)
    {   

        $training_file_id = $request->get('training_file_id');
        $training_file = TrainingFile::find($training_file_id);
        
        //if record is empty then display error page
        if(empty($training_file->id))
        {
            return abort(404, 'Not Found');
        }

        $training_file->delete();

        $file_path = $training_file->file_path;

        $path = public_path() . $file_path . "/" . $training_file->file_name;
        unlink($path);

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function download(Request $request)
    {   
        try {

            $file = TrainingFile::find($request->id);

            $title = $file->title; 
            $file_path = $file->file_path;    
            $file_name = $file->file_name;
            $file_type = $file->file_type;

            $file = public_path() . $file_path . "/" . $file_name;
            $headers = array('Content-Type: application/' . $file_type,);
            // return Response::download($file, 'Branch Company.csv', $headers);
            return response()->download($file, $title . '.' . $file_type, $headers);

        } catch (\Exception $e) {
            
            return response()->json(['error' => $e->getMessage()], 200);
        }
        
    }
}

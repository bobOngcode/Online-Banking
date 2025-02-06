<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Auth;
use Crypt;
use App\SapDatabase;
use App\SapDbBranch;

class SAPDatabaseController extends Controller
{
    public function index()
    {       
        $sap_databases = SAPDatabase::with('sap_db_branches')
                                    ->with('sap_db_branches.branch')
                                    ->with('sap_db_branches.sap_database')
                                    ->select('id', 'server', 'database', 'username', 'active')->get();
        return response()->json(['sap_databases' => $sap_databases], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
        
        $rules = [
            'server.required' => 'Server is required',
            'database.required' => 'Database Name is required',
            'username.required' => 'Username is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be atleast 8 characters',
            'password.same' => 'Password and Confirm Password did not match',
            'confirm_password.required' => 'Confirm Password is required',
        ];

        $valid_fields = [
            'server' => 'required|string|max:255',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string|min:8|same:confirm_password',
            'confirm_password' => 'required',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {   
            return response()->json($validator->errors(), 200);
        }

        $sap_database_ctr = SAPDatabase::where('server', $request->get('server'))
                                   ->where('database', $request->get('database'))
                                   ->count();
        if($sap_database_ctr)
        {
            return response()->json(['error' => 'SAP Database already exists'], 200);
        }

        $sap_database = new SAPDatabase();
        $sap_database->server = $request->get('server');
        $sap_database->database = $request->get('database');
        $sap_database->username = $request->get('username');
        $sap_database->password = Crypt::encrypt($request->get('password'));
        $sap_database->active = $request->get('active');
        $sap_database->save();

        foreach ($request->sap_db_branches as $key => $branch) {
            
            SapDbBranch::create([
                'branch_id' => $branch,
                'sap_database_id' => $sap_database->id,
            ]);
        
        }

        $sap_db = SAPDatabase::with('sap_db_branches')
                            ->with('sap_db_branches.branch')
                            ->with('sap_db_branches.sap_database')
                            ->select('id', 'server', 'database', 'username', 'active')
                            ->where('id', '=', $sap_database->id)
                            ->first();

        return response()->json(['success' => 'Record has successfully added', 'sap_database' => $sap_db], 200);
    }


    public function edit(Request $request)
    {   
        $sap_database_id = $request->get('sap_database_id');

        $sap_database = SAPDatabase::find($sap_database_id);

        //if record is empty then display error page
        if(empty($sap_database->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json(['sap_database' => $sap_database], 200);

    }


    public function update(Request $request, $sap_database_id)
    {   
     
        $rules = [
            'server.required' => 'Server is required',
            'database.required' => 'Database Name is required',
            'username.required' => 'Username is required',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be atleast 8 characters',
            'password.same' => 'Password and Confirm Password did not match',
            'confirm_password.required' => 'Confirm Password is required',
        ];

        $valid_fields = [
            'server' => 'required|string|max:255',
            'database' => 'required|string',
            'username' => 'required|string',
        ];
        
        if($request->get('password') || $request->get('confirm_password'))
        {
            $valid_fields['password'] = 'required|string|min:8|same:confirm_password';
            $valid_fields['confirm_password'] = 'required';
        }

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $sap_database = SAPDatabase::find($sap_database_id);

        //if record is empty then display error page
        if(empty($sap_database->id))
        {
            return abort(404, 'Not Found');
        }

        $sap_database->server = $request->get('server');
        $sap_database->database = $request->get('database');
        $sap_database->username = $request->get('username');
        $sap_database->active = $request->get('active');
        
        if(!empty($request->get('password')))
        {
            $sap_database->password = Crypt::encrypt($request->get('password'));;
        }

        $sap_database->save();

        $arrBranch = SapDbBranch::where('sap_database_id', $sap_database_id)->pluck('branch_id')->toArray();

        $sap_db_branches = $request->sap_db_branches;

        foreach ($sap_db_branches as $key => $branch) { 
        
            if(!in_array($branch, $arrBranch))
            {
                SapDbBranch::create([
                    'branch_id' => $branch,
                    'sap_database_id' => $sap_database_id,
                ]);
            }

        }

        SapDbBranch::where('sap_database_id', $sap_database_id)
                   ->whereNotIn('branch_id', $sap_db_branches)
                   ->delete();
        
        $sap_database = SAPDatabase::with('sap_db_branches')
                                   ->where('id', $sap_database_id)
                                   ->select('id', 'server', 'database', 'username', 'active')
                                   ->first();

        return response()->json([
            'success' => 'Record has been updated',
            'sap_database' => $sap_database,
            ]
        );
    }


    public function delete(Request $request)
    {   
        $sap_database_id = $request->get('sap_database_id');
        $sap_database = SAPDatabase::find($sap_database_id);
        
        //if record is empty then display error page
        if(empty($sap_database_id))
        {
            return abort(404, 'Not Found');
        }

        $sap_database->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

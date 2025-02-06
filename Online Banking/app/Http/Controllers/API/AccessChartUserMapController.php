<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AccessModule;
use App\AccessChart;
use App\AccessChartUserMap;
use App\AccessLevel;
use App\User;
use Validator;
use DB;

class AccessChartUserMapController extends Controller
{
    
    public function store(Request $request)
    {
        
        $rules = [
            'access_chart_id.required' => 'Access Chart ID is required',
            'access_chart_id.integer' => 'Access Chart ID must be integer',
            'user_id.required' => 'User ID is required',
            'user_id.integer' => 'User ID must be integer',
            'access_level.required' => 'Access Level is required',
            'access_level.integer' => 'Access Level must be integer',
        ];

        $validator = Validator::make($request->all(),[
            'access_chart_id' => 'required|integer',
            'user_id' => 'required|integer',
            'access_level' => 'required|integer'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $approver = new AccessChartUserMap();
        $approver->access_chart_id = $request->get('access_chart_id');
        $approver->user_id = $request->get('user_id');
        $approver->access_level = $request->get('access_level');
        $approver->save();

        $approver_id = $approver->id;

        $approver = AccessChartUserMap::with('user')->where('id', '=', $approver_id)->first();

        $max_access_level = $this->max_access_level($request->get('access_chart_id'));
        
        return response()->json(['success' => 'Record has been added', 'approver' => $approver, 'max_access_level' => $max_access_level], 200);
    }

    public function edit($access_chart_id)
    {   
        $access_chart_id = $request->get('access_chart_id');

        $access_module = AccessModule::find($access_module_id);

        //if record is empty then display error page
        if(empty($access_module->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json(['access_module' => $access_module], 200);

    }

    public function update(Request $request, $approver_id)
    {
        
        $rules = [
            'user_id.required' => 'User ID is required',
            'user_id.integer' => 'User ID must be integer',
            'access_level.required' => 'Access Level is required',
            'access_level.integer' => 'Access Level must be integer',
        ];

        $validator = Validator::make($request->all(),[
            'user_id' => 'required|integer',
            'access_level' => 'required|integer'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $approver = AccessChartUserMap::find($approver_id);
        $approver->user_id = $request->get('user_id');
        $approver->access_level = $request->get('access_level');
        $approver->save();

        $approver = AccessChartUserMap::with('user')
                                      ->with('user.branch')
                                      ->where('id', '=', $approver_id)
                                      ->first();
                                      
        $max_access_level = $this->max_access_level($approver->access_chart_id);

        return response()->json([
            'success' => 'Record has been updated', 
            'approver' => $approver, 
            'max_access_level' => $max_access_level
        ], 200);
    }

    public function delete(Request $request)
    {   
        $approver_id = $request->get('approver_id');
        $approver = AccessChartUserMap::find($approver_id);
        
        //if record is empty then display error page
        if(empty($approver->id))
        {
            return abort(404, 'Not Found');
        }

        $approver->delete();

        $max_access_level = $this->max_access_level($approver->access_chart_id);

        return response()->json(['success' => 'Record has been deleted', 'max_access_level' => $max_access_level], 200);
    }

    public function max_access_level($access_chart_id)
    {
        $max_access_level = AccessChartUserMap::where('access_chart_id', '=', $access_chart_id)->max('access_level');

        return $max_access_level;
    }
}

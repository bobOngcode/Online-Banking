<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AccessModule;
use App\AccessChart;
use App\AccessChartUserMap;
use App\AccessLevel;
use App\ApproverPerLevel;
use App\User;
use Validator;
use DB;

class AccessChartController extends Controller
{
    public function index()
    {
        $access_charts = AccessChart::with('access_chart_user_maps')
                                    ->with('access_chart_user_maps.user')
                                    ->with('access_chart_user_maps.user.branch')
                                    ->with('access_module')
                                    ->with('approver_per_level')
                                    ->get();
        
        $access_modules = AccessModule::all();
        $access_level = AccessLevel::first();
        $users = User::all();                                             
        
        return response()->json(
            [
                'access_charts' => $access_charts, 
                'access_modules' => $access_modules, 
                'access_level' => $access_level,
                'users' => $users,
            ], 200);
    }

    public function create()
    {

    }

    public function store(Request $request)
    {
        
        $rules = [
            'name.required' => 'Please enter name',
            'name.unique' => 'Access Chart Name already exists',
            'access_for.required' => 'Access ID is required',
            'access_for.integer' => 'Access ID must be integer',
            'max_approval_level.integer' => 'Max Approval Level must be integer',
            'approver_per_level.*.num_of_approvers' => 'No. of Approvers must be integer',
            'approver_per_level.*.level' => 'Level must be integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:access_charts,name',
            'access_for' => 'required|integer',
            'max_approval_level' => 'nullable|integer',
            'approver_per_level.*.num_of_approvers' => 'nullable|integer',
            'approver_per_level.*.level' => 'nullable|integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $access_chart = new AccessChart();
        $access_chart->name = $request->get('name');
        $access_chart->access_for = $request->get('access_for');
        $access_chart->max_approval_level = $request->get('max_approval_level');
        $access_chart->save();

        $approver_per_level = $request->get('approver_per_level');

        foreach ($approver_per_level as $i => $value) {
            $approver_per_level = new ApproverPerLevel();
            $approver_per_level->module_id = $request->get('access_for');
            $approver_per_level->level = $value['level'];
            $approver_per_level->num_of_approvers = $value['num_of_approvers'];
            $approver_per_level->save();
        }

        $access_chart = AccessChart::with('access_chart_user_maps')
                                    ->with('access_chart_user_maps.user')
                                    ->with('access_chart_user_maps.user.branch')
                                    ->with('access_module')
                                    ->with('approver_per_level')
                                    ->where('id', '=', $access_chart->id)
                                    ->first();

        return response()->json(['success' => 'Record has been added', 'access_chart' => $access_chart], 200);
    }

    public function edit($access_chart_id)
    {   
        $access_module = AccessModule::find($access_chart_id);

        //if record is empty then display error page
        if(empty($access_module->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json(['access_module' => $access_module], 200);

    }

    public function update(Request $request, $access_chart_id)
    {
        
        $rules = [
            'name.required' => 'Please enter name',
            'name.unique' => 'Access Chart Name already exists',
            'access_for.required' => 'Access ID is required',
            'access_for.integer' => 'Access ID must be integer',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:access_charts,name,'.$access_chart_id,
            'access_for' => 'required|integer'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $access_chart = AccessChart::find($access_chart_id);
        $access_chart->name = $request->get('name');
        $access_chart->access_for = $request->get('access_for');
        $access_chart->save();

        $approver_per_level = $request->get('approver_per_level');
        
        $approver_per_level_1_count = ApproverPerLevel::where('module_id', '=', $request->get('access_for'))->get()->count();
        $approver_per_level_2_count = count($approver_per_level);

        foreach ($approver_per_level as $i => $value) {
            $approver_per_level = ApproverPerLevel::find($value['id']);

            // if approver_per_level exists on the table then update
            if($approver_per_level)
            {
                $approver_per_level->module_id = $request->get('access_for');
                $approver_per_level->level = $value['level'];
                $approver_per_level->num_of_approvers = $value['num_of_approvers'];
            }
            else // if approver_per_level does not exist on the table then add
            {
                $approver_per_level = new ApproverPerLevel();
                $approver_per_level->module_id = $request->get('access_for');
                $approver_per_level->level = $value['level'];
                $approver_per_level->num_of_approvers = $value['num_of_approvers'];
            }
            $approver_per_level->save();
        }

        $access_chart = AccessChart::with('access_chart_user_maps')
                                    ->with('access_chart_user_maps.user')
                                    ->with('access_chart_user_maps.user.branch')
                                    ->with('access_module')
                                    ->where('id', '=', $access_chart_id)
                                    ->first();

        return response()->json(
            [
                'success' => 'Record has been added', 
                'access_chart' => $access_chart,
                $approver_per_level_1_count,
                $approver_per_level_2_count
            ], 200);
    }

    public function delete(Request $request)
    {   
        $access_chart_id = $request->get('access_chart_id');
        $access_chart = AccessChart::find($access_chart_id);
        
        //if record is empty then display error page
        if(empty($access_chart->id))
        {
            return abort(404, 'Not Found');
        }

        $access_chart->delete();

        $access_chart_user_map = AccessChartUserMap::where('access_chart_id', '=', $access_chart_id);
        $access_chart_user_map->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function update_access_level(Request $request, $access_level_id)
    {   
        $rules = [
            'level.required' => 'Access Level is required',
            'level.integer' => 'Access Level must be an integer',   
        ];

        $validator = Validator::make($request->all(), [
            'level' => 'required|integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $access_level = AccessLevel::find($access_level_id);
        $access_level->level = $request->get('level');
        $access_level->save();

        return response()->json(['success' => 'Record has been added', 'access_level' => $access_level], 200);
    }

    public function get_access_level()
    {
        $access_level = AccessLevel::first();

        return response()->json(['access_level' => $access_level], 200);
    }


}

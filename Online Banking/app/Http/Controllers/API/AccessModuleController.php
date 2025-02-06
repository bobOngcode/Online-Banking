<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AccessModule;
use Validator;
use DB;

class AccessModuleController extends Controller
{
    public function index()
    {       
        $access_modules = AccessModule::all();
        return response()->json(['access_modules' => $access_modules], 200);
    }

    public function create()
    {
        
    }

    public function store(Request $request)
    {   
        
        $rules = [
            'name.required' => 'Please enter name',
            'name.unique' => 'Module Name already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:access_modules,name'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $access_module = new AccessModule();
        $access_module->name = $request->get('name');
        $access_module->save();

        return response()->json(['success' => 'Record has successfully added', 'access_module' => $access_module], 200);
    }


    public function edit($access_module_id)
    {   
        $access_module_id = $request->get('access_module_id');

        $access_module = AccessModule::find($access_module_id);

        //if record is empty then display error page
        if(empty($access_module->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json(['access_module' => $access_module], 200);

    }


    public function update(Request $request, $access_module_id)
    {   

        $rules = [
            'name.required' => 'Please enter brand',
            'name.unique' => 'Module Name already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:access_modules,name,'.$access_module_id
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $access_module = AccessModule::find($access_module_id);

        //if record is empty then display error page
        if(empty($access_module->id))
        {
            return abort(404, 'Not Found');
        }

        $access_module->name = $request->get('name');
        $access_module->save();


        return response()->json([
            'success' => 'Record has been updated',
            'access_module' => $access_module]
        );
    }


    public function delete(Request $request)
    {   
        $access_module_id = $request->get('access_module_id');
        $access_module = AccessModule::find($access_module_id);
        
        //if record is empty then display error page
        if(empty($access_module->id))
        {
            return abort(404, 'Not Found');
        }

        $access_module->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Division;
use Validator;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::all();

        return response()->json(['divisions' => $divisions], 200);
    }


    public function store(Request $request)
    {   
        // return $request;
        $rules = [
            'division.required' => 'Division is required',
            'division.unique' => 'Division already exists'
        ];

        $validator = Validator::make($request->all(),[
            'division' => 'required|unique:divisions,name'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $division = new Division();
        $division->name = $request->get('division');
        $division->active = $request->get('active');
        $division->save();

        return response()->json(['success' => 'Record has successfully added', 'division' => $division], 200);
    }

    public function update(Request $request, $division_id)
    {   
        // return $request;
        $rules = [
            'division.required' => 'Division is required',
            'division.unique' => 'Division already exists'
        ];

        $validator = Validator::make($request->all(),[
            'division' => 'required|unique:divisions,name,'.$division_id
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $division = Division::find($division_id);
        $division->name = $request->get('division');
        $division->active = $request->get('active');
        $division->save();

        return response()->json(['success' => 'Record has successfully added', 'division' => $division], 200);
    }

    public function delete(Request $request)
    {
        $division = Division::find($request->get('division_id'));
        //if record is empty then display error page
        if(empty($division->id))
        {
            return abort(404, 'Not Found');
        }
        $division->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

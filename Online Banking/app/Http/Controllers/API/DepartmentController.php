<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Department;
use App\Division;
use Validator;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('division')->get();
        $divisions = Division::all();

        return response()->json(['departments' => $departments, 'divisions' => $divisions], 200);
    }


    public function store(Request $request)
    {   
        // return $request;
        $rules = [
            'department.required' => 'Department is required',
            'department.unique' => 'Department already exists',
            'division_id.required' => 'Division is required',
            'division.integer' => 'Division value must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'department' => 'required|unique:departments,name',
            'division' => 'required.integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $department = new Department();
        $department->name = $request->get('department');
        $department->division_id = $request->get('division_id');
        $department->active = $request->get('active');
        $department->save();

        return response()->json(['success' => 'Record has successfully added', 'department' => $department], 200);
    }

    public function update(Request $request, $department_id)
    {   
        // return $request;
        $rules = [
            'department.required' => 'Department is required',
            'department.unique' => 'Department already exists',
            'division_id.required' => 'Division is required',
            'division.integer' => 'Division value must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'department' => 'required|unique:departments,name,'.$department_id,
            'division' => 'required.integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $department = Department::find($department_id);
        $department->name = $request->get('department');
        $department->division_id = $request->get('division_id');
        $department->active = $request->get('active');
        $department->save();

        return response()->json(['success' => 'Record has successfully added', 'department' => $department], 200);
    }

    public function delete(Request $request)
    {
        $department = Department::find($request->get('department_id'));
        //if record is empty then display error page
        if(empty($department->id))
        {
            return abort(404, 'Not Found');
        }
        $department->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Branch;
use App\Company;
use App\SapDatabase;
use Validator;
use DB;

class BranchController extends Controller
{
    public function index()
    {       

        $branches = Branch::with('company')->orderBy('name')->get();
        $companies = Company::all();
        return response()->json(['branches' => $branches, 'companies' => $companies], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
        
        $rules = [
            'name.required' => 'Please enter branch',
            'name.unique' => 'Branch already exists',
            'code.required' => 'Please enter branch code',
            'code.unique' => 'Branch Code already exists',
            'company_id.required' => 'Please enter company',
            'company_id.integer' => 'Company must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:branches,name',
            'code' => 'required|unique:branches,code',
            'company_id' => 'required|integer',

        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $branch = new Branch();
        $branch->name = $request->get('name');
        $branch->code = $request->get('code');
        $branch->bm_oic = $request->get('bm_oic');
        $branch->company_id = $request->get('company_id');
        $branch->save();

        return response()->json(['success' => 'Record has successfully added', 'branch' => $branch], 200);
    }


    public function edit(Request $request)
    {   
        $branch_id = $request->get('branch_id');

        $branch = Branch::find($branch_id);
        
        //if record is empty then display error page
        if(empty($branch->id))
        {
            return abort(404, 'Not Found');
        }
        
        // return view('pages.service.edit', compact('service'));
        return response()->json(['branch' => $branch], 200);

    }


    public function update(Request $request, $branch_id)
    {   

        $rules = [
            'name.required' => 'Please enter branch',
            'name.unique' => 'Branch already exists',
            'code.required' => 'Please enter branch code',
            'code.unique' => 'Branch Code already exists',
            'company_id.required' => 'Please enter company',
            'company_id.integer' => 'Company must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:branches,name,'.$branch_id,
            'code' => 'required|unique:branches,code,'.$branch_id,
            'company_id' => 'required|integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $branch = Branch::find($branch_id);

        //if record is empty then display error page
        if(empty($branch->id))
        {
            return abort(404, 'Not Found');
        }

        $branch->name = $request->get('name');
        $branch->code = $request->get('code');
        $branch->bm_oic = $request->get('bm_oic');
        $branch->company_id = $request->get('company_id');
        $branch->save();


        return response()->json([
            'success' => 'Record has been updated',
            'branch' => $branch]
        );
    }


    public function delete(Request $request)
    {   
        $branch_id = $request->get('branch_id');
        $branch = Branch::find($branch_id);
        
        //if record is empty then display error page
        if(empty($branch->id))
        {
            return abort(404, 'Not Found');
        }

        $branch->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

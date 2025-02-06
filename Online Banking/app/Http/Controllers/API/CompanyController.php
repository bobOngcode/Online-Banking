<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Company;
use Validator;
use DB;

class CompanyController extends Controller
{
    public function index()
    {       
        $companies = Company::with('branches')->get();
        return response()->json(['companies' => $companies], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
        
        $rules = [
            'name.required' => 'Please enter company',
            'name.unique' => 'Company already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:companies,name'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $company = new Company();
        $company->name = $request->get('name');
        $company->active = $request->get('active');
        $company->save();

        return response()->json(['success' => 'Record has successfully added', 'company' => $company], 200);
    }


    public function edit(Request $request)
    {   
        $company_id = $request->get('company_id');

        $company = Company::find($company_id);

        //if record is empty then display error page
        if(empty($company->id))
        {
            return abort(404, 'Not Found');
        }
        
        // return view('pages.service.edit', compact('service'));
        return response()->json(['company' => $company], 200);

    }


    public function update(Request $request, $company_id)
    {   

        $rules = [
            'name.required' => 'Please enter company',
            'name.unique' => 'Company already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:companies,name,'.$company_id
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $company = Company::find($company_id);

        //if record is empty then display error page
        if(empty($company->id))
        {
            return abort(404, 'Not Found');
        }

        $company->name = $request->get('name');
        $company->active = $request->get('active');
        $company->save();


        return response()->json([
            'success' => 'Record has been updated',
            'company' => $company]
        );
    }


    public function delete(Request $request)
    {   
        $company_id = $request->get('company_id');
        $company = Company::find($company_id);
        
        //if record is empty then display error page
        if(empty($company->id))
        {
            return abort(404, 'Not Found');
        }

        $company->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

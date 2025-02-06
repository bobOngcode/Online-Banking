<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Brand;
use Validator;
use DB;

class BrandController extends Controller
{
    public function index()
    {       
        $brands = Brand::orderBy('name')->get();
        return response()->json(['brands' => $brands], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
        
        $rules = [
            '*.name.required' => 'Please enter brand',
            'name.unique' => 'Brand already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:brands,name'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $brand = new Brand();
        $brand->name = $request->get('name');
        $brand->active = $request->get('active');
        $brand->save();

        return response()->json(['success' => 'Record has successfully added', 'brand' => $brand], 200);
    }


    public function edit(Request $request)
    {   
        $brand_id = $request->get('brand_id');

        $brand = Brand::find($brand_id);

        //if record is empty then display error page
        if(empty($brand->id))
        {
            return abort(404, 'Not Found');
        }
        
        // return view('pages.service.edit', compact('service'));
        return response()->json(['brand' => $brand], 200);

    }


    public function update(Request $request, $brand_id)
    {   

        $rules = [
            'name.required' => 'Please enter brand',
            'name.unique' => 'Brand already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:brands,name,'.$brand_id
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $brand = Brand::find($brand_id);

        //if record is empty then display error page
        if(empty($brand->id))
        {
            return abort(404, 'Not Found');
        }

        $brand->name = $request->get('name');
        $brand->active = $request->get('active');
        $brand->save();


        return response()->json([
            'success' => 'Record has been updated',
            'brand' => $brand]
        );
    }


    public function delete(Request $request)
    {   
        $brand_id = $request->get('brand_id');
        $brand = Brand::find($brand_id);
        
        //if record is empty then display error page
        if(empty($brand->id))
        {
            return abort(404, 'Not Found');
        }

        $brand->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

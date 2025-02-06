<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ProductCategory;
use Validator;
use DB;

class ProductCategoryController extends Controller
{
    public function index()
    {       
        $product_categories = ProductCategory::all();
        return response()->json(['product_categories' => $product_categories], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
        
        $rules = [
            'name.required' => 'Please enter product category',
            'name.unique' => 'Product Category already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:product_categories,name'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $product_category = new ProductCategory();
        $product_category->name = $request->get('name');
        $product_category->active = $request->get('active');
        $product_category->save();

        return response()->json(['success' => 'Record has successfully added', 'product_category' => $product_category], 200);
    }


    public function edit(Request $request)
    {   
        $product_category_id = $request->get('product_category_id');

        $product_category = ProductCategory::find($product_category_id);

        //if record is empty then display error page
        if(empty($product_category->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json(['product_category' => $product_category], 200);

    }


    public function update(Request $request, $product_category_id)
    {   

        $rules = [
            'name.required' => 'Please enter product category',
            'name.unique' => 'Product Category already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:product_categories,name,'.$product_category_id
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $product_category = ProductCategory::find($product_category_id);

        //if record is empty then display error page
        if(empty($product_category->id))
        {
            return abort(404, 'Not Found');
        }

        $product_category->name = $request->get('name');
        $product_category->active = $request->get('active');
        $product_category->save();


        return response()->json([
            'success' => 'Record has been updated',
            'product_category' => $product_category]
        );
    }


    public function delete(Request $request)
    {   
        $product_category_id = $request->get('product_category_id');
        $product_category = ProductCategory::find($product_category_id);
        
        //if record is empty then display error page
        if(empty($product_category->id))
        {
            return abort(404, 'Not Found');
        }

        $product_category->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

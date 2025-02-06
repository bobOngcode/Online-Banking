<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ProductModel;
use Validator;
use DB;

class ProductModelController extends Controller
{
    public function index(Request $request)
    {           
        
        $product_models = ProductModel::where('name', 'like', '%'.$request->search.'%')
                                      ->orderBy('name', 'asc')
                                      ->paginate($request->items_per_page);

        return response()->json(['product_models' => $product_models], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
        
        $rules = [
            'name.required' => 'Please enter product model',
            'name.unique' => 'Product Model already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:product_models,name'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $product_model = new ProductModel();
        $product_model->name = $request->get('name');
        $product_model->active = $request->get('active');
        $product_model->save();

        return response()->json(['success' => 'Record has successfully added', 'product_model' => $product_model], 200);
    }


    public function edit(Request $request)
    {   
        $product_model_id = $request->get('product_model_id');

        $product_model = ProductModel::find($product_model_id);

        //if record is empty then display error page
        if(empty($product_model->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json(['product_model' => $product_model], 200);

    }


    public function update(Request $request, $product_model_id)
    {   

        $rules = [
            'name.required' => 'Please enter product model',
            'name.unique' => 'Product Model already exists'
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:product_models,name,'.$product_model_id
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $product_model = ProductModel::find($product_model_id);

        //if record is empty then display error page
        if(empty($product_model->id))
        {
            return abort(404, 'Not Found');
        }

        $product_model->name = $request->get('name');
        $product_model->active = $request->get('active');
        $product_model->save();


        return response()->json([
            'success' => 'Record has been updated',
            'product_model' => $product_model]
        );
    }


    public function delete(Request $request)
    {   
        $product_model_id = $request->get('product_model_id');
        $product_model = ProductModel::find($product_model_id);
        
        //if record is empty then display error page
        if(empty($product_model->id))
        {
            return abort(404, 'Not Found');
        }

        $product_model->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

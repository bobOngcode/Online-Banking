<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ExpenseParticular;
use Validator;
use DB;

class ExpenseParticularController extends Controller
{
    public function index()
    {       
        $expense_particulars = ExpenseParticular::all();
        return response()->json(['expense_particulars' => $expense_particulars], 200);
    }

    public function create()
    {
        
    }

    public function store(Request $request)
    {   
        
        $rules = [
            'description.required' => 'Please enter description',
            'description.unique' => 'Description already exists'
        ];

        $validator = Validator::make($request->all(),[
            'description' => 'required|unique:expense_particulars,description'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $expense_particular = new ExpenseParticular();
        $expense_particular->description = $request->get('description');
        $expense_particular->active = $request->get('active');
        $expense_particular->save();

        return response()->json(['success' => 'Record has successfully added', 'expense_particular' => $expense_particular], 200);
    }


    public function edit(Request $request)
    {   
        $expense_particular_id = $request->get('expense_particular_id');

        $expense_particular = ExpenseParticular::find($expense_particular_id);

        //if record is empty then display error page
        if(empty($expense_particular->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json(['expense_particular' => $expense_particular], 200);

    }


    public function update(Request $request, $expense_particular_id)
    {   

        $rules = [
            'description.required' => 'Please enter description',
            'description.unique' => 'Description already exists'
        ];

        $validator = Validator::make($request->all(),[
            'description' => 'required|unique:expense_particulars,description,'.$expense_particular_id
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $expense_particular = ExpenseParticular::find($expense_particular_id);

        //if record is empty then display error page
        if(empty($expense_particular->id))
        {
            return abort(404, 'Not Found');
        }

        $expense_particular->description = $request->get('description');
        $expense_particular->active = $request->get('active');
        $expense_particular->save();


        return response()->json([
            'success' => 'Record has been updated',
            'expense_particular' => $expense_particular]
        );
    }


    public function delete(Request $request)
    {   
        $expense_id = $request->get('expense_id');
        $expense_particular = ExpenseParticular::find($expense_id);
        
        //if record is empty then display error page
        if(empty($expense_particular->id))
        {
            return abort(404, 'Not Found');
        }

        $expense_particular->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

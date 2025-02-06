<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Position;
use App\Rank;
use App\Branch;
use App\RequiredEmployeeMap;
use Validator;
use DB;

class PositionController extends Controller
{
    public function index()
    {       
        $positions = Position::with('rank')
                             ->with('required_employees')
                             ->with('required_employees.branch')
                             ->get();
        $ranks = Rank::all();
        $branches = Branch::orderBy('name', 'ASC')->get();

        // foreach ($positions as $position) {
        //     foreach ($branches as $branch) {
        //         RequiredEmployeeMap::create([
        //             'branch_id' => $branch->id,
        //             'position_id' => $position->id,
        //             'quantity' => rand(1,20)
        //         ]);
        //     }
        // }

        return response()->json(['positions' => $positions, 'ranks' => $ranks, 'branches' => $branches], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
      
        $rules = [
            'name.required' => 'Please enter position',
            'name.unique' => 'Position already exists',
            'rank_id.required' => 'Rank is required',
            'rank.integer' => 'Rank must be an integer',
            'cost_center.required' => 'Please enter Cost Center',
            'branchRequirement.*.branch_id.required' => 'Branch is required',
            'branchRequirement.*.branch_id.integer' => 'Branch ID must be an integer',
            'branchRequirement.*.quantity.required' => 'Quantity is required',
            'branchRequirement.*.quantity.integer' => 'Quantity must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:positions,name',
            'rank_id' => 'required|integer',
            'cost_center' => 'required',
            'branchRequirement.*.branch_id' => 'required|integer',
            'branchRequirement.*.quantity' => 'required|integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $position = new Position();
        $position->name = $request->get('name');
        $position->rank_id = $request->get('rank_id');
        $position->cost_center = $request->get('cost_center');
        $position->save();

        // branchRequirement is list of required employees per branch
        foreach ($request->branchRequirement as $key => $value) {
            RequiredEmployeeMap::create([
                'position_id' => $position->id,
                'branch_id' => $value['branch_id'],
                'quantity' => $value['quantity']
            ]);
        }

        $position = Position::with('rank')
                            ->with('required_employees')
                            ->with('required_employees.branch')
                            ->find($position->id);

        return response()->json(['success' => 'Record has successfully added', 'position' => $position], 200);
    }


    public function edit(Request $request)
    {   
        $position_id = $request->get('position_id');

        $position = Position::find($position_id);

        //if record is empty then display error page
        if(empty($position->id))
        {
            return abort(404, 'Not Found');
        }
        
        // return view('pages.service.edit', compact('service'));
        return response()->json(['position' => $position], 200);

    }


    public function update(Request $request, $position_id)
    {   

        $rules = [
            'name.required' => 'Please enter position',
            'name.unique' => 'Position already exists',
            'rank_id.required' => 'Rank is required',
            'rank.integer' => 'Rank must be an integer',
            'cost_center.required' => 'Please enter Cost Center',
            'branchRequirement.*.branch_id.required' => 'Branch is required',
            'branchRequirement.*.branch_id.integer' => 'Branch ID must be an integer',
            'branchRequirement.*.quantity.required' => 'Quantity is required',
            'branchRequirement.*.quantity.integer' => 'Quantity must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:positions,name,'.$position_id,
            'rank_id' => 'required|integer',
            'cost_center' => 'required',
            'branchRequirement.*.branch_id' => 'required|integer',
            'branchRequirement.*.quantity' => 'required|integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $position = Position::find($position_id);

        //if record is empty then display error page
        if(empty($position->id))
        {
            return abort(404, 'Not Found');
        }

        $position->name = $request->get('name');
        $position->rank_id = $request->get('rank_id');
        $position->cost_center = $request->get('cost_center');
        $position->save();

        // branchRequirement is list of required employees per branch
        foreach ($request->branchRequirement as $key => $value) {

            // updateOrCreate params ([conditions], [new value])
            RequiredEmployeeMap::updateOrCreate([
                    'position_id' => $position->id, 
                    'branch_id' => $value['branch_id'],
                ],
                ['quantity' => $value['quantity']] //new value
            );
        }

        $position = Position::with('rank')
                            ->with('required_employees')
                            ->with('required_employees.branch')
                            ->find($position->id);

        return response()->json([
            'success' => 'Record has been updated',
            'position' => $position]
        );
    }


    public function delete(Request $request)
    {   
        $position_id = $request->get('position_id');
        $position = Position::find($position_id);
        
        //if record is empty then display error page
        if(empty($position->id))
        {
            return abort(404, 'Not Found');
        }

        $position->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

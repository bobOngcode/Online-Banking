<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Rank;
use Validator;

class RankController extends Controller
{
    public function index()
    {       
        $ranks = Rank::all();
        return response()->json(['ranks' => $ranks], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
        
        $rules = [
            'name.required' => 'Please enter rank',
            'name.unique' => 'Rank already exists',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:ranks,name',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $rank = new Rank();
        $rank->name = $request->get('name');
        $rank->save();

        return response()->json(['success' => 'Record has successfully added', 'rank' => $rank], 200);
    }


    public function edit(Request $request)
    {   
        $rank_id = $request->get('rank_id');

        $rank = Rank::find($rank_id);

        //if record is empty then display error page
        if(empty($rank->id))
        {
            return abort(404, 'Not Found');
        }
        
        // return view('pages.service.edit', compact('service'));
        return response()->json(['rank' => $rank], 200);

    }


    public function update(Request $request, $rank_id)
    {   

        $rules = [
            'name.required' => 'Please enter rank',
            'name.unique' => 'Rank already exists',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:ranks,name,'.$rank_id,
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $rank = Rank::find($rank_id);

        //if record is empty then display error page
        if(empty($rank->id))
        {
            return abort(404, 'Not Found');
        }

        $rank->name = $request->get('name');
        $rank->save();


        return response()->json([
            'success' => 'Record has been updated',
            'rank' => $rank]
        );
    }


    public function delete(Request $request)
    {   
        $rank_id = $request->get('rank_id');
        $rank = Rank::find($rank_id);
        
        //if record is empty then display error page
        if(empty($rank->id))
        {
            return abort(404, 'Not Found');
        }

        $rank->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}

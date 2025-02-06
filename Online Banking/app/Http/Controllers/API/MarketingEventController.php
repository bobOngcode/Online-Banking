<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ExpenseParticular;
use App\ExpenseSubParticular;
use App\MarketingEvent;
use App\AccessLevel;
use App\User;
use Validator;
use DB;

class MarketingEventController extends Controller
{
    public function index()
    {       
        $marketing_events = MarketingEvent::with('expense_particulars')
                                          ->with('expense_particulars.expense_sub_particulars')
                                          ->with('marketing_event_user_maps')
                                          ->with('marketing_event_user_maps.user')
                                          ->with('marketing_event_user_maps.user.branch')
                                          ->with('approver_per_level')
                                          ->get();

        $access_level = AccessLevel::first();
        $users = User::all();

        return response()->json([
            'marketing_events' => $marketing_events,
            'access_level' => $access_level,
            'users' => $users,
        ], 200);
    }

    public function create()
    {
        
    }

    public function store(Request $request)
    {   
        return $request;
        $rules = [
            'event_name.required' => 'Marketing Event Name is required',
            'event_name.unique' => 'Marketing Event Name already exists',
            'attachment_required.required' => 'Attachment Required status is required',
            'expense_particulars.*.description.required' => 'Description is required',
            'expense_particulars.*.children.*.description.required' => 'Description is required',
        ];

        $validator = Validator::make($request->all(), [
            'event_name' => 'required|unique:marketing_events,event_name',
            'attachment_required' => 'required',
            'expense_particulars.*.description' => 'required',
            'expense_particulars.*.children.*.description' => 'required',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        // return response()->json($request->all(), 200);

        $marketing_event = new MarketingEvent();
        $marketing_event->event_name = $request->get('event_name');
        $marketing_event->attachment_required = $request->get('attachment_required');
        $marketing_event->active = 'Y';
        $marketing_event->save();

        $expense_particulars = $request->get('expense_particulars');
        
        foreach ($expense_particulars as $key => $value) {
            
            $expense_particular = new ExpenseParticular();
            $expense_particular->marketing_event_id = $marketing_event->id;
            $expense_particular->description = $value['description'];
            $expense_particular->active = 'Y';
            $expense_particular->dynamic = $value['dynamic'];
            $expense_particular->save();
            
            foreach($value['children'] as $i => $val)
            {
                $expense_sub_particular = new ExpenseSubParticular();
                $expense_sub_particular->expense_particular_id = $expense_particular->id;
                $expense_sub_particular->description = $val['description'];
                $expense_sub_particular->active = 'Y';
                $expense_sub_particular->save();
            }
        }

        $marketing_event = MarketingEvent::with('expense_particulars')
                                         ->where('id', '=', $marketing_event->id);

        return response()->json(['success' => 'Record has successfully added', 'marketing_event' => $marketing_event], 200);
    }


    public function edit(Request $request)
    {   
        $marketing_event_id = $request->get('marketing_event_id');

        $marketing_event = MarketingEvent::with('expense_particulars')
                                          ->with('expense_particulars.expense_sub_particulars')
                                          ->where('id', '=', $marketing_event_id)
                                          ->first();

        //if record is empty then display error page
        if(empty($marketing_event->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json(['marketing_event' => $marketing_event], 200);

    }


    public function update(Request $request, $marketing_event_id)
    {   

        $rules = [
            'event_name.required' => 'Marketing Event Name is required',
            'event_name.unique' => 'Marketing Event Name already exists',
            'attachment_required.required' => 'Attachment Required status is required',
            'expense_particulars.*.description.required' => 'Description is required',
            'expense_particulars.*.children.*.description.required' => 'Description is required',
        ];

        $validator = Validator::make($request->all(), [
            'event_name' => 'required|unique:marketing_events,event_name,'.$marketing_event_id,
            'attachment_required' => 'required',
            'expense_particulars.*.description' => 'required',
            'expense_particulars.*.children.*.description' => 'required',
        ], $rules);


        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }
        
        $marketing_event = MarketingEvent::find($marketing_event_id);

        //if record is empty then display error page
        if(empty($marketing_event->id))
        {
            return abort(404, 'Not Found');
        }

        $marketing_event->event_name = $request->get('event_name');
        $marketing_event->attachment_required = $request->get('attachment_required');
        $marketing_event->save();
        
        $expense_particulars = $request->get('expense_particulars');

        // update expense_particulars and expense_sub_particulars
        foreach ($expense_particulars as $key => $value) {
            
            $expense_particular = new ExpenseParticular();

            // if array has id then updated table
            if(isset($value['id']))
            {
                $expense_particular = ExpenseParticular::find($value['id']);

                //if record is empty then display error page
                if(empty($expense_particular->id))
                {
                    return abort(404, 'Not Found');
                } 

            }
  
            $expense_particular->marketing_event_id = $marketing_event_id;
            $expense_particular->description = $value['description'];
            $expense_particular->dynamic = $value['dynamic'];
            $expense_particular->active = 'Y';
            $expense_particular->save();

            foreach ($value['children'] as $key => $val) {
                // if array has id then updated table
                if(isset($val['id']))
                {   
                    $expense_sub_particular = ExpenseSubParticular::find($val['id']);
    
                    //if record is empty then display error page
                    if(empty($expense_sub_particular->id))
                    {
                        return abort(404, 'Not Found');
                    }

                    $expense_sub_particular->description = $val['description'];
                    $expense_sub_particular->save();
                }
                // if array has not id then then store the data
                else
                {   
                    $expense_sub_particular = new ExpenseSubParticular();
                    $expense_sub_particular->expense_particular_id = $expense_particular->id;
                    $expense_sub_particular->description = $val['description'];
                    $expense_sub_particular->active = 'Y';
                    $expense_sub_particular->save();
                }
            }
            
        }


        $deleted_fields = $request->get('deletedRows');

        // deleted expense particulars
        foreach ($deleted_fields as $key => $value) {

            if($value['deletedRow'] === 'expense_particulars')
            {
                $expense_particular = ExpenseParticular::find($value['id']);

                //if record is empty then display error page
                if(empty($expense_particular->id))
                {
                    return abort(404, 'Not Found');
                }

                $expense_particular->delete();
                
                // delete all child data when parent is deleted
                ExpenseSubParticular::where('expense_particular_id', '=', $value['id'])->delete();

            }

            if($value['deletedRow'] === 'expense_sub_particulars')
            {
                $expense_sub_particular = ExpenseSubParticular::find($value['id']);

                //if record is empty then display error page
                if(empty($expense_sub_particular->id))
                {
                    return abort(404, 'Not Found');
                }

                $expense_sub_particular->delete();
            }
        }

        return response()->json([
            'success' => 'Record has been updated',
            'merketing_event' => $marketing_event]
        );
    }


    public function delete(Request $request)
    {   
        $marketing_event_id = $request->get('marketing_event_id');
        $marketing_event = MarketingEvent::find($marketing_event_id);
        
        //if record is empty then display error page
        if(empty($marketing_event->id))
        {
            return abort(404, 'Not Found');
        }

        $marketing_event->delete();

        $expense_particular = ExpenseParticular::select('id')
                                               ->where('marketing_event_id', '=', $marketing_event_id)->get();

        ExpenseParticular::where('marketing_event_id', '=', $marketing_event_id)->delete();
        ExpenseSubParticular::whereIn('expense_particular_id', $expense_particular)->delete();
        
        return response()->json(['success' => 'Record has been deleted', $expense_particular], 200);
    }
}

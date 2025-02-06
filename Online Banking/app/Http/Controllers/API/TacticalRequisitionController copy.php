<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Branch;
use App\MarketingEvent;
use App\TacticalRequisition;
use App\TacticalRequisitionRow;
use App\TacticalRequisitionSubRow;
use App\TacticalRequisitionAttachment;
use App\AccessChart;
use App\AccessChartUserMap;
use App\ApprovedLog;
use Validator;
use DB;
use Carbon\Carbon;

class TacticalRequisitionController extends Controller
{   

    public function index() 
    {   

        $user_can_view_tactical_list = Auth::user()->can('tactical-requisition-list');

        // Access chart Tactical Requisition
        $access_chart = AccessChart::with('access_chart_user_maps')
                                   ->with('access_module')
                                   ->with('approver_per_level')
                                //    ->where('access_for', '=', 5) //ID of Tactital Requisition from Access Module
                                   ->whereHas('access_module', function($query){
                                         $query->where('name', '=', 'Tactical Requisition');
                                   })
                                   ->first();
        
        $approvers = $access_chart->access_chart_user_maps;

        $approver_per_level = $access_chart->approver_per_level;

        // check if Auth is approver on access chart tactical requisition
        $approver_ctr = AccessChart::with('access_chart_user_maps')
                                    ->whereHas('access_module', function($query){
                                        $query->where('name', '=', 'Tactical Requisition');
                                    })
                                    ->whereHas('access_chart_user_maps', function($query){
                                        $query->whereIn('user_id', [Auth::id()]);
                                    })->get()->count();
        
        $tactical_requisitions = TacticalRequisition::with('branch')
                                                    ->with('user')
                                                    ->with('marketing_event')
                                                    ->with('approved_logs')
                                                    ->with('approved_logs.approver')
                                                    ->selectRaw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created")
                                                    ->whereHas('user', function($query) use ($approver_ctr, $user_can_view_tactical_list){    
                                                        
                                                        // if approver_ctr === 0 (no approver) or not admin user then select record where user_id == Auth::id()
                                                        if($approver_ctr === 0)
                                                        {   
                                                            if(Auth::id() != 1)
                                                            {
                                                                $query->where('id', '=', Auth::id());
                                                            }
                                                        }

                                                    })
                                                    ->get();      

        $data = $tactical_requisitions;

        // get the current level approvers
        $curr_level_approvers = [];

        $approval_progress = [];
        
        // get the required current level approvers for each record
        foreach ($tactical_requisitions as $key => $value) {
            $level = [];

            foreach ($approver_per_level as $key2 => $value2) {
            
                $approver_ctr_per_level = 0;
                
                // approved_logs for each record
                foreach ($value->approved_logs as $key3 => $value3) {

                    // scan if approved_logs is equal to approver_per_level (from access_charts table relations)
                    if($value2->level === $value3->level)
                    {   
                        // count the logs of approver per level for each record - used to validate if tactical requisition has already approved by the required approver for each level
                        $approver_ctr_per_level += 1;

                        $approver [] = $value3->approver->name;
                    }
                }
                
                // get all approval level of un approved tactical requisitions
                if($apprvr_per_lvl->num_of_approvers > $approver_ctr_per_level)
                {
                    $level [] = $apprvr_per_lvl->level;
                }
                
            }

            $approvers_arr = [];
            // get the approver id (user_id) where level == min($level)
            foreach ($approvers as $key2 => $value2) {

                if($value2->access_level === min($level))
                {
                    $approvers_arr[] = $value2->user_id;
                }
            }

            $curr_level_approvers [] = ['tactical_requisition_id' => $value->id, 'level' => min($level), 'approver_id' => $approvers_arr];

        }
        
        
        
        // if user is approver 
        if($approver_ctr > 0)
        {
            $tactical_requisitions = [];

            // filter record if approver has already approved the record
            foreach ($data as $key => $value) {

                $approved_by_user = false;
                $current_level_approver = false;

                foreach ($value->approved_logs as $key2 => $value2) {
                    if(Auth::id() ===  $value2->approver_id)
                    {
                        $approved_by_user = true;                        
                    }
                }

                // check if user(approver) is the current level approver
                foreach ($curr_level_approvers as $key2 => $value2) {
                    if($value2['tactical_requisition_id'] === $value->id && in_array(Auth::id(), $value2['approver_id']) )
                    {
                        $current_level_approver = true;
                    }
                }
                
                // if Auth is hasnt approved the record and is current approver level
                if(!$approved_by_user && $current_level_approver)
                {
                    $tactical_requisitions[] = $value;
                }
                
            }

            // filter record if approver is on the current level to approve
        }
    
        $approval_progress = [];
        
        // get the approval progress per record
        foreach ($tactical_requisitions as $key => $value) {
            $progress = [];

            foreach ($approver_per_level as $key2 => $value2) {
            
                $approver_ctr_per_level = 0;
                $approver = [];
                $done = false;
                
                // approved_logs for each record
                foreach ($value->approved_logs as $key3 => $value3) {
                    if($value2->level === $value3->level)
                    {
                        $approver_ctr_per_level += 1;
                        $approver [] = $value3->approver->name;
                    }
                }
    
                if($approver_ctr_per_level >= $apprvr_per_lvl->num_of_approvers)
                {
                    $done = true;
                }
    
                $progress [] = ['level' => $value2->level, 'done' => $done, 'approver' => $approver];
            }

            $approval_progress [] = ['tactical_requisition_id' => $value->id, 'progress' => $progress];

        }
                                     
        return response()->json([
            'tactical_requisitions' => $tactical_requisitions, 
            'approval_progress' => $approval_progress,
            'approved_logs' => $tactical_requisitions,
        ], 200);
    }

    public function create() 
    {
        $branches = Branch::all();
        $marketing_events = MarketingEvent::with('expense_particulars')
                                          ->with('expense_particulars.expense_sub_particulars')
                                          ->get();
        
        return response()->json(['branches' => $branches, 'marketing_events' => $marketing_events], 200);
    } 

    public function tactical_validator($data, $action)
    {
        
        $rules = [
            'branch_id.required' => 'Branch ID is required',
            'branch_id.integer' => 'Branch ID must be an integer',
            'marketing_event_id.required' => 'Marketing Event ID is required',
            'marketing_event_id.integer' => 'Marketing Event ID must be an integer',
            'venue.required' => 'Venue is required',
            'sponsor.required' => 'Sponsor is required',
            'period_from.required' => 'Period Date From is required',
            'period_from.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'period_to.required' => 'Period Date From is required',
            'period_to.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'operating_from.required' => 'Operating Hour From is required',
            'operating_to.required' => 'Operating Hour To is required',
            'prev_period_from.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'prev_period_to.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'prev_quota.numeric' => 'Enter a valid value',
            'prev_quota.between' => 'Enter a valid value',
            'prev_total_sales.numeric' => 'Enter a valid value',
            'prev_total_sales.between' => 'Enter a valid value',
            'prev_sales_achievement.numeric' => 'Enter a valid value',
            'prev_sales_achievement.between' => 'Enter a valid value',
            'prev_total_expense.numeric' => 'Enter a valid value',
            'prev_total_expense.between' => 'Enter a valid value',
            'expense_particulars.required' => 'Expense Particulars is required',

        ];

        $valid_fields = [
            'venue' => 'required',
            'sponsor' => 'required',
            'period_from' => 'required|date_format:Y-m-d',
            'period_to' => 'required|date_format:Y-m-d',
            'operating_from' => 'required',
            'operating_to' => 'required',
            'prev_period_from' => 'date_format:Y-m-d|nullable',
            'prev_period_to' => 'date_format:Y-m-d|nullable',
            'prev_quota' => 'numeric|between:0, 999999.99|nullable',
            'prev_total_sales' => 'numeric|between:0, 999999.99|nullable',
            'prev_sales_achievement' => 'numeric|between:0, 999999.99|nullable',
            'prev_total_expense' => 'numeric|between:0, 999999.99|nullable',
            'expense_particulars' => 'required',
        ];

        if($action === 'add')
        {
            $valid_fields['branch_id'] = 'required|integer';
            $valid_fields['marketing_event_id'] = 'required|integer';
        }

        return $validator = Validator::make($data, $valid_fields, $rules);

    }

    public function tactical_requisition($data, $tactical_requisition, $action)
    {
        if($action === 'add')
        {
            $tactical_requisition->branch_id = $data->branch_id;
            $tactical_requisition->user_id = Auth::id();
            $tactical_requisition->marketing_event_id = $data->marketing_event_id;
        }
        
        $tactical_requisition->sponsor = $data->sponsor;
        $tactical_requisition->venue = $data->venue;
        $tactical_requisition->period_from = $data->period_from;
        $tactical_requisition->period_to = $data->period_from;
        $tactical_requisition->operating_to = $data->operating_to;
        $tactical_requisition->operating_from = $data->operating_from;
        $tactical_requisition->prev_period_from = $data->prev_period_from;
        $tactical_requisition->prev_period_to = $data->prev_period_to;
        $tactical_requisition->prev_venue = $data->prev_venue;
        $tactical_requisition->prev_sponsor = $data->prev_sponsor;
        $tactical_requisition->prev_quota = (float)$data->prev_quota;
        $tactical_requisition->prev_total_sales = (float)$data->prev_total_sales;
        $tactical_requisition->prev_sales_achievement = (float)$data->prev_sales_achievement;
        $tactical_requisition->prev_total_expense = (float)$data->prev_total_expense;
        $tactical_requisition->status = 'Pending';
        $tactical_requisition->date_approve = null;
        $tactical_requisition->save();

        return $tactical_requisition;
    }

    public function store(Request $request)
    {   

        $req = (array)json_encode($request->all()); //convert stringify into array
        $data = [];

        foreach (json_decode($req[0]) as $field => $value) {
            $data[$field] = ($field != 'file') ? json_decode($value) : $value ;
        }  

        // call validator public function tactical_validator
        $validator = $this->tactical_validator($data, 'add');
        
        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $data = (object)$data; //convert array to object

        $tactical_requisition = new TacticalRequisition();
        
        // call public function tactical_requisition 
        $tactical_requisition_id = $this->tactical_requisition($data, $tactical_requisition, 'add')->id;

        $expense_particulars = $data->expense_particulars;
 
        foreach ($expense_particulars as $key => $value) {
            $tactical_row = new TacticalRequisitionRow();
            $tactical_row->tactical_requisition_id = $tactical_requisition_id;
            $tactical_row->line_num = $key;
            $tactical_row->description = $value->description;
            $tactical_row->resource_person = $value->resource_person;
            $tactical_row->contact = $value->contact;
            $tactical_row->qty = (integer)$value->qty;
            $tactical_row->unit_cost = (float)$value->unit_cost;
            $tactical_row->amount = (float)$value->amount;
            $tactical_row->save();
            
            $sub_rows = $value->expense_sub_particulars;
            
            // if has sub items
            if(count($sub_rows))
            {   
                foreach ($sub_rows as $i => $val) {
                    $tactical_sub_row = new TacticalRequisitionSubRow();
                    $tactical_sub_row->tactical_requisition_row_id = $tactical_row->id;
                    $tactical_sub_row->line_num = $i;
                    $tactical_sub_row->description = $val->description;
                    $tactical_sub_row->resource_person = $val->resource_person;
                    $tactical_sub_row->contact = $val->contact;
                    $tactical_sub_row->qty = (integer)$val->qty;
                    $tactical_sub_row->unit_cost = (float)$val->unit_cost;
                    $tactical_sub_row->amount = (float)$val->amount;
                    $tactical_sub_row->save();
                } 
            }
        }

        $tactical_requisition = TacticalRequisition::with('tactical_rows')
                                         ->with('tactical_rows.tactical_sub_rows')
                                         ->with('tactical_attachments')
                                         ->where('id', '=', $tactical_requisition_id)
                                         ->first();
        
        $files = $request->file;

        // call public function add_file
        $this->add_file($request, $tactical_requisition_id);

        return response()->json(['success' => 'Record has successfully added', 'tactical_requisition' => $tactical_requisition], 200);
    }

    public function add_file(Request $request, $tactical_requisition_id)
    {   
        $files = $request->file;

        if(is_array($files))
        {
            foreach ($files as $key => $file) {
                
                try {
                    $file_extension = $file->getClientOriginalExtension();
                    $file_date = Carbon::now()->format('Y-m-d');
                    $file_name = time().$file->getClientOriginalName();
                    $file_path = '/wysiwyg/tactical_attachment/' . $file_date;

                    $tactical_attachment = new TacticalRequisitionAttachment();
                    $tactical_attachment->tactical_requisition_id = $tactical_requisition_id;
                    $tactical_attachment->file_name = $file_name;
                    $tactical_attachment->file_path = $file_path;
                    $tactical_attachment->file_type = $file_extension;
                    $tactical_attachment->title = $file_name;
                    $tactical_attachment->save();

                    $file->move(public_path() . $file_path, $file_name);
                    
                } catch (\Exception $e) {
                
                    return response()->json(['error' => $e->getMessage()], 200);
                }
            }
        }

        $tactical_attachments = TacticalRequisitionAttachment::where('tactical_requisition_id', '=', $tactical_requisition_id)->get();

        return response()->json(['success' => 'File has been added', 'tactical_attachments' => $tactical_attachments ], 200);

    }

    public function edit(Request $request)
    {
        $branches = Branch::all();
        $marketing_events = MarketingEvent::with('expense_particulars')
                                          ->with('expense_particulars.expense_sub_particulars')
                                          ->get();
        
        $tactical_requisition = TacticalRequisition::with('tactical_rows')
                                                    ->with('tactical_rows.tactical_sub_rows')
                                                    ->with('tactical_attachments')
                                                    ->with('branch')
                                                    ->with('user')
                                                    ->with('marketing_event')
                                                    ->with('approved_logs')
                                                    ->with('approved_logs.approver')
                                                    ->where('id', '=', $request->get('tactical_requisition_id'))
                                                    ->first();

        return response()->json([
            'tactical_requisitions' => $tactical_requisition, 
            'branches' => $branches, 
            'marketing_events' => $marketing_events,
        ], 200);
    }

    public function update(Request $request, $tactical_requisition_id)
    {   

        // call validator public function tactical_validator
        $validator = $this->tactical_validator($request->all(), 'edit');
        
        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }
        
        $tactical_requisition = TacticalRequisition::find($tactical_requisition_id);

        // call public function tactical_requisition 
        $this->tactical_requisition($request, $tactical_requisition, 'edit');

        $expense_particulars = $request->get('expense_particulars');

        foreach ($expense_particulars as $key => $value) {
            $tactical_row = TacticalRequisitionRow::find($value['tactical_requisition_row_id']);
            $tactical_row->description = $value['description'];
            $tactical_row->resource_person = $value['resource_person'];
            $tactical_row->contact = $value['contact'];
            $tactical_row->qty = $value['qty'];
            $tactical_row->unit_cost = $value['unit_cost'];
            $tactical_row->amount = $value['amount'];
            $tactical_row->save();
            
            $sub_rows = $value['expense_sub_particulars'];
            
            // if has sub items
            if(count($sub_rows))
            {   
                foreach ($sub_rows as $i => $val) {
                    $tactical_sub_row = TacticalRequisitionSubRow::find($val['tactical_requisition_sub_row_id']);
                    $tactical_sub_row->description = $val['description'];
                    $tactical_sub_row->resource_person = $val['resource_person'];
                    $tactical_sub_row->contact = $val['contact'];
                    $tactical_sub_row->qty = $val['qty'];
                    $tactical_sub_row->unit_cost = $val['unit_cost'];
                    $tactical_sub_row->amount = $val['amount'];
                    $tactical_sub_row->save();
                } 
            }
        }

        $tactical_requisition = TacticalRequisition::with('tactical_rows')
                                         ->with('tactical_rows.tactical_sub_rows')
                                         ->with('tactical_attachments')
                                         ->where('id', '=', $tactical_requisition_id)
                                         ->first();

        return response()->json(['success' => 'Record has successfully added', 'tactical_requisition' => $tactical_requisition]);
    }

    public function delete(Request $request)
    {   
        $tactical_requisition_id = $request->get('tactical_requisition_id');
        $tactical_requisition = TacticalRequisition::find($tactical_requisition_id);
        
        //if record is empty then display error page
        if(empty($tactical_requisition->id))
        {
            return abort(404, 'Not Found');
        }

        $tactical_requisition->delete();

        $tactical_requisition_row_ids = TacticalRequisitionRow::where('tactical_requisition_id', '=', $tactical_requisition_id)->pluck('id');

        TacticalRequisitionRow::where('tactical_requisition_id', '=', $tactical_requisition_id)->delete();
        TacticalRequisitionSubRow::whereIn('tactical_requisition_row_id', $tactical_requisition_row_ids)->delete();
        TacticalRequisitionAttachment::where('tactical_requisition_id', '=', $tactical_requisition_id)->delete();

        return response()->json(['success' => 'Recard has been deleted'],200);
    }

    public function delete_file(Request $request)
    {
        $file_id = $request->get('file_id');
        $file = TacticalRequisitionAttachment::find($file_id);
        
        //if record is empty then display error page
        if(empty($file->id))
        {
            return abort(404, 'Not Found');
        }

        $file->delete();

        $file_path = $file->file_path;

        $path = public_path() . $file_path . "/" . $file->file_name;
        unlink($path);

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function approve(Request $request, $tactical_requisition_id)
    {
        $user_can_approve_tactical = Auth::user()->can('tactical-requisition-approve');

        // get access chart tactical requisition approver
        $access_chart = AccessChart::with('access_chart_user_maps')
                                    ->whereHas('access_chart_user_maps', function($query){
                                        $query->whereIn('user_id', [Auth::id()]);
                                    })
                                    ->where('name', '=', 'Tactical Requisition')
                                    ->get();

        $approver_ctr = $access_chart->count();

        if($approver_ctr=== 0 || !$user_can_approve_tactical)
        {
            return response()->json(['error' => "You don't have permission to do this action", $approver_ctr], 200);
        }

        $access_level = AccessChart::with('access_chart_user_maps')
                                     ->where('name', '=', 'Tactical Requisition')
                                     ->first()
                                     ->access_chart_user_maps
                                     ->where('user_id', Auth::id())
                                     ->first()
                                     ->access_level;
                                     
        $approved_log = new ApprovedLog();
        $approved_log->module_id = $access_chart->access_for; //Tactical Requisition
        $approved_log->document_id = $tactical_requisition_id;
        $approved_log->approver_id = Auth::id();
        $approved_log->level = $access_level;
        // $approved_log->save();

        // get the number of max level approvers
        $approved_logs = ApprovedLog::where('document_id', '=', $tactical_requisition_id)
                                    ->where('level', '=', 4)
                                    ->get()->count();


        $tactical_requisition = TacticalRequisition::find($tactical_requisition_id);
        $tactical_requisition->status = 'Approved';
        $tactical_requisition->save();

        return response()->json([
            'success' => 'Record has been approved'
        ], 200);

    }
}

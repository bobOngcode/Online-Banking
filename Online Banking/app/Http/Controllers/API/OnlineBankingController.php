<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AccessModule;
use App\OnlineBank;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\AccessChart;
use App\ModuleActivityLog;
use app\AccessChartUserMap;
use App\ApproverPerLevel;
use App\ApprovedLog;
use App\UserSignature;
use App\User;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;



class OnlineBankingController extends Controller
{
    public function module()
    {
        return AccessModule::where('name', '=', 'Online Banking')->get()->first();
    }

    public function index()
    {

        $username = '';


        //get the access chart id
        $access_chart = AccessChart::where('name', 'Online Banking')
            ->pluck('id')
            ->first();


        // check if Auth is approver
        $approver_ctr = AccessChart::with('access_chart_user_maps')
            ->whereHas('access_chart_user_maps', function ($query) use ($access_chart) {
                $query->where('user_id', Auth::id())
                    ->where('access_chart_id', $access_chart);
            })->get()->count();



        if (!$approver_ctr) {
            $username = User::with(['e_signature' => function ($query) {
                $query->select('e_signature', 'user_id')->where('user_id', Auth::id());
            }])
                ->where('id', Auth::id())
                ->get()
                ->each(function ($user) {
                    $user->e_signature = $user->e_signature ?: null;
                });
        }



        // get the current user access level
        $user_access_level = AccessChartUserMap::where('user_id', Auth::id())
            ->where('access_chart_id', $access_chart)
            ->pluck('access_level')
            ->first();



        // get the id of module specified
        $module_id = $this->module()->id;



        // filter the request forms
        $bankingrequest = OnlineBank::with('user.e_signature', 'user', 'access_chart', 'access_chart.access_chart_user_maps', 'access_chart.approver_per_level', 'approved_logs', 'approved_logs.approver')
            ->selectRaw("*, DATE_FORMAT(bdate, '%Y-%m-%d') as bdate, DATE_FORMAT(date_started, '%Y-%m-%d') as date_started, DATE_FORMAT(date_ended, '%Y-%m-%d') as date_ended, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created, DATE_FORMAT(date_approve, '%m/%d/%Y') as date_approved")
            ->orderBy('created_at', 'Desc');



        if (!$approver_ctr && Auth::id() > 1) {

            // If not an approver, filter by the user's own requests (excluding admin/root user)
            $bankingrequest->where('user_id', Auth::id());
        }



        $bankingrequest = $bankingrequest->get();



        $data = $bankingrequest;



        // get the current level approvers
        $curr_level_approvers = [];



        //get the approval progress 
        $approval_progress = [];



        // get the required current level approvers for each record
        foreach ($bankingrequest as $banking) {

            $level = [];

            // get the approver per level
            $approver_per_level = $banking->access_chart ? $banking->access_chart->approver_per_level : [];


            foreach ($approver_per_level as $apprvr_per_lvl) {

                $approver_ctr_per_level = 0; //count tha approve levels of document

                // approved_logs for each record
                foreach ($banking->approved_logs as $log) {

                    // scan if approved_logs is equal to approver_per_level (from access_charts table relations)
                    if ($apprvr_per_lvl->level === $log->level) {
                        // count the logs of approver per level for each record - used to validate if banking form has already approved by the required approver for each level
                        $approver_ctr_per_level += 1;
                    }
                }

                // get all approval level of un approved banking request form
                if ($apprvr_per_lvl->num_of_approvers > $approver_ctr_per_level) {
                    $level[] = $apprvr_per_lvl->level;
                }
            }

            $approvers_arr = [];
            $current_level =  $banking->access_chart ? $banking->access_chart->max_approval_level : 0; //$access_chart->max_approval_level;


            // if banking request status is Pending or On Process then set the required level approver to show the for approval banking request
            if (in_array($banking->status, ['Pending', 'On Process'])) {
                $current_level = count($level) ? min($level) : 1;
            }


            $approvers =  $banking->access_chart ? $banking->access_chart->access_chart_user_maps : [];


            // get the approver id (user_id) where level == min($level)
            foreach ($approvers as $approver) {

                if ($approver->access_level === $current_level) {
                    $approvers_arr[] = $approver->user_id;
                }
            }


            $curr_level_approvers[] = ['banking_id' => $banking->id, 'level' => $current_level, 'approver_id' => $approvers_arr];
        }





        $user = Auth::user();

        // if user is approver 
        if ($approver_ctr > 0 && !$user->hasRole('Online Banking Approver')) {

            // list of tactical requisitions if auth() is an approver
            $online_banking = []; // if user is approver then reset the $online_banking

            // filter record if approver has already approved the record
            foreach ($data as $banking) {

                $approved_by_user = false;
                $current_level_approver = false;

                // check if user(approver) already approved the record based from approved_logs table
                foreach ($banking->approved_logs as $log) {

                    if (Auth::id() ===  $log->approver_id) {

                        $approved_by_user = true;
                    }
                }


                // check if user(approver) is the current level approver
                foreach ($curr_level_approvers as $approver) {

                    if ($approver['banking_id'] === $banking->id && in_array(Auth::id(), $approver['approver_id'])) {

                        $current_level_approver = true;
                    }
                }


                // if Auth is hasnt approved the record and is current approver level
                if (!$approved_by_user && $current_level_approver) {

                    $online_banking[] = $banking;
                }
            }
        }




        $approval_progress = [];
        $pending_approval_progress = [];
        $onprocess_approval_progress = [];
        $approved_approval_progress = [];
        $disapproved_approval_progress = [];




        // get the approval progress per record
        foreach ($bankingrequest as $banking) {
            $progress = [];
           
            $isDisapproved = false;
            $approver_per_level = $banking->access_chart ?  $banking->access_chart->approver_per_level : [];

            foreach ($approver_per_level as $key => $apprvr_per_lvl) {
                $approver_ctr_per_level = 0;
                $approver = [];
                $status = "Pending";


                // approved_logs for each record
                foreach ($banking->approved_logs as $log) {
                    if ($apprvr_per_lvl->level === $log->level) {
                        $approver_ctr_per_level += 1;
                        $approver[] = $log->approver->name;
                    }
                }


                if ($approver_ctr_per_level >= $apprvr_per_lvl->num_of_approvers) {
                    $status = "Approved";
                } else if ($banking->status === 'Disapproved') {

                    $status = "Disapproved";

                    if (!$isDisapproved) {
                        $approver[] = ApprovedLog::with('approver')
                            ->where('module_id', '=', $this->module()->id)
                            ->where('document_id', '=', $banking->id)
                            ->where('level', '=', 0)
                            ->get()->first()
                            ->approver->name;
                    }

                    $isDisapproved = true;
                }

                if ($banking->status === 'Pending' || $banking->status === 'On Process') {
                    $pending_progress[] = ['level' => $apprvr_per_lvl->level, 'status' => $status, 'approver' => $approver];
                }


                if ($banking->status === 'On Process') {
                    $onprocess_progress[] = ['level' => $apprvr_per_lvl->level, 'status' => $status, 'approver' => $approver];
                }


                else if ($banking->status === 'Approved') {
                    $approved_progress[] = ['level' => $apprvr_per_lvl->level, 'status' => $status, 'approver' => $approver];
                } 


                else if ($banking->status === 'Disapproved') {
                    $disapproved_progress[] = ['level' => $apprvr_per_lvl->level, 'status' => $status, 'approver' => $approver];
                }


                $progress[] = ['level' => $apprvr_per_lvl->level, 'status' => $status, 'approver' => $approver];
            }

            $approval_progress[] = ['banking_request_id' => $banking->id, 'progress' => $progress];

        }



        $pendings = [];
        $onprocess = [];
        $approves = [];
        $disapproves = [];




        // get the by category status
        foreach ($bankingrequest as $banking) {


            // PENDING
            if ($banking->status == 'Pending' || $banking->status == 'On Process' && $banking->approve_level === $user_access_level) {


                if ($banking->status == 'Pending') {

                    if ($banking->status == 'Pending' && $user_access_level <= 1) {

                        $pendings[] = $banking;
                    } else if ($banking->status == 'On Process') {

                        $pendings[] = $banking;
                    }
                } else if ($user_access_level != 1) {

                    $pendings[] = $banking;
                    
                }

            }


            // ON PROCESS
            if ($banking->status == 'On Process') {

                if ($banking->approve_level > $user_access_level) {
                    $onprocess[] = $banking;
                }
            }


            // APPROVED
            else if ($banking->status == 'Approved') {

                if ($banking->approve_level >= $user_access_level) {
                    $approves[] = $banking;
                }
            }


            // DISAPPROVED
            else if ($banking->status == 'Disapproved') {

                $disapproves[] = $banking;
            }
        }




        // return $bankingrequest;
        return response()->json([
            'user_current_lvl' => $user_access_level,
            'pendings' => $pendings,
            'onprocess' => $onprocess,
            'approves' => $approves,
            'disapproves' => $disapproves,
            'approval_progress' => $approval_progress,
            'username' => $username
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        // return $request->banking_form;


        $rules = [

            // first row 
            'banking_form' => 'required',
            'date_now' => 'required',

            // second row
            'company' => 'required',
            'name' => 'required',
            'position' => 'required',

            // third row
            'bank_name' => 'required',
            'account_no' => 'required',

        ];

        $userhas_signature = UserSignature::where('user_id', Auth::id())->first();

        if (!$userhas_signature) {
            $rules = array_merge($rules, [
                'e_signature' => 'required|image',
            ]);
        }

        //check if the status for update is true or false
        if ($request->input('DELETION') === 'false') {

            // dd('pakyu');
            $rules = array_merge($rules, [
                'birthdate' => 'required',
                'email' => 'required|email',
                'contact' => 'required',
                'tin' => 'required',
                'sss' => 'required',
                'philhealth' => 'required',
                'amount_limit' => 'required',
            ]);

            //HENCE REQUIRED TYPE
            // $rules = array_merge($rules, [
            //     'type' => 'required',
            // ]);

            ///check if update type if ACCOUNT NUMBER
            if ($request->type === 'ACCOUNT #') {

                //HENCE REQUIRED TYPE
                $rules = array_merge($rules, [
                    'account_from' => 'required',
                    'account_to' => 'required',
                ]);
            }

            //ASSIGN BANK VALUE
            $bank = $request->bdo_type ? 'BDO' : ($request->mbtc_type ? 'MBTC' : false);



            //SET AS REQUIRED
            $rules = array_merge($rules, [
                'bank' => 'required',
            ]);

            //CHECK IF THE BANK IS MBOSS / MBTC
            // if ($bank === 'MBTC') {
            //     // dd($bank);
            //     //HENCE REQUIRED THE AUTHENTICATOR ID
            //     $rules = array_merge($rules, [
            //         'auth_id' => 'required',
            //     ]);
            // }
        }



        if ($request->input('mbtc_type') === 'true') {

            $rules = array_merge($rules, [
                'auth_id' => 'required',
            ]);
        }


        $validator = Validator::make(
            $request->all(),
            $rules,
            [

                'banking_form.required' => 'Please select at least one option (New Maker, New Verifier, or For Deletion).',
                'date_now.required' => 'Please provide a date.',

                'company.required' => 'Please provide your company name.',
                'name.required' => 'Please provide your name.',
                'position.required' => 'Please provide your position.',
                'birthdate.required' => 'Please provide your birthdate.',
                'birthdate.date' => 'Please provide a valid date for your birthdate.',
                'contact.required' => 'Please provide your cellular number.',
                'email.required' => 'Please provide your email address.',
                'email.email' => 'Please provide a valid email address.',
                'tin.required' => 'Please provide your TIN #.',
                'sss.required' => 'Please provide your SSS #.',
                'philhealth.required' => 'Please provide your PhilHealth #.',

                'type.required' => 'Please specify the update type.',
                'account_from.required' => 'Please specify the account #.',
                'account_to.required' => 'Please specify the account #.',
                'bank.required' => 'Please select at least one option (BDO or MBTC).',
                'auth_id.required' => 'Please provide your authorization ID.',

                'bank_name.required' => 'Please provide the bank name.',
                'account_no.required' => 'Please provide the account #.',
                'amount_limit.required' => 'Please provide the amount limit.',

                'e_signature.required' => 'Please provide your e-signature.'
            ]
        );



        // RETURN IF INVALID
        if ($validator->fails()) {
            // return response()->json($validator->errors(), 200);
            if ($validator->fails()) {
                $errors = $validator->errors();

                // Remove 'banking_form.' prefix from each error key
                $transformedErrors = [];
                foreach ($errors->getMessages() as $field => $messages) {
                    // Remove the 'banking_form.' prefix
                    // $fieldName = str_replace('banking_form.', '', $field);
                    $transformedErrors[$field] = $messages;
                }


                return response()->json(['errors' => $transformedErrors], 200);
            }
        }

        //pass the data to a variable for shorter the calling initial
        $data = $request->input('banking_form');

        // save the created for to datatbase  with a default  status 'Pending' 
        $online_banking = new OnlineBank();
        $online_banking->user_id = Auth::user()->id; //assign the id of current user
        $online_banking->access_module_id = $this->module()->id; // assign the module id to identify which modulte the request assigned
        $online_banking->formtype = $request->banking_form;
        $online_banking->submitted_at = Carbon::now()->format('Y-m-d');
        $online_banking->company = $request->company;
        $online_banking->position = $request->position;
        $online_banking->name = $request->name;
        $online_banking->bdate = $request->birthdate;
        $online_banking->email = $request->email;
        $online_banking->contact = $request->contact;
        $online_banking->tin = $request->tin;
        $online_banking->sss = $request->sss;
        $online_banking->philhealth = $request->philhealth;
        $online_banking->update_type = $request->type;
        $online_banking->from_acct = $request->type === 'ACCOUNT #' ? $request->account_from : null;
        $online_banking->to_acct = $request->type === 'ACCOUNT #' ? $request->account_to : null;
        $online_banking->bank = $request->bank;
        $online_banking->auth_id = $request->auth_id;
        $online_banking->bank_name = $request->bank_name;
        $online_banking->acct_no = $request->account_no;
        $online_banking->check_series = $request->check_series;
        $online_banking->amount_limit = $request->amount_limit;
        $online_banking->allowed_access1 = $request->allow_access1 ? 1 : 0;
        $online_banking->allowed_access2 = $request->allow_access2 ? 1 : 0;
        $online_banking->date_started = $request->date_started;
        $online_banking->date_ended = $request->date_ended;
        $online_banking->status = 'Pending';
        $online_banking->save();


        $file = $request->file('e_signature');


        if ($file) {
            $user_name = User::where('id', Auth::id())->first();
            $file_extension = $file->getClientOriginalExtension();
            $file_name = 'e_signature-' . $user_name->name . '.' . $file_extension;
            $file_path = '/img/e_signature';
            $user_signature = new UserSignature();
            $user_signature->user_id = Auth::user()->id;
            $user_signature->e_signature = $file_name;
            $user_signature->file_path = $file_path;
            $user_signature->file_type = $file_extension;
            $user_signature->save();

            $file->move(public_path() . $file_path, $file_name);

            // $directory = public_path() . $file_path;


            // Full path to the image
            // $full_image_path = $directory . '/' . $file_name;


            // Remove the background from the image
            // $this->removeBackground($full_image_path);
        }


        // $this->removeBackground($file_path);


        if ($online_banking) {
            return response()->json(['success' => true, 'icon' => 'success', 'title' => 'Banking Request Success', 'text' => 'Banking Request Submitted Successfully', 'online_banking_form' => $online_banking]);
        } else {
            return response()->json(['success' => false, 'icon' => 'error', 'title' => 'System Error', 'text' => 'Pleas contact Administrator']);
        }
    }


    private function removeBackground($filePath)
    {

        $image = imagecreatefrompng($filePath);
        if (!$image) {
            $image = imagecreatefromgif($filePath);
        }

        if (!$image) {
            return;
        }


        // Get image dimensions
        $width = imagesx($image);
        $height = imagesy($image);


        // Loop through each pixel and remove white color
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $color = imagecolorat($image, $x, $y);
                $rgb = imagecolorsforindex($image, $color);

                // Check if the pixel is white
                if ($rgb['red'] == 255 && $rgb['green'] == 255 && $rgb['blue'] == 255) {
                    imagesetpixel($image, $x, $y, imagecolortransparent($image, $color));
                }
            }
        }


        // Save the modified image back
        imagepng($image, $filePath);
        // Free up memory
        imagedestroy($image);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function approve(Request $request)
    {
        // dd($request->id);

        // get the request id
        $banking_request_id = $request->get('id');


        // get the current date
        $date_now = Carbon::now()->format('Y-m-d');


        // get the permisision
        $user_can_approve_banking = Auth::user()->can('online-banking-approve');


        // get the specific banking item
        $banking_request = OnlineBank::where('id', '=', $banking_request_id)
            ->with('access_chart')
            ->with('access_chart.access_chart_user_maps')
            ->with('access_chart.access_chart_user_maps.user')
            ->get()->first();


        // get the of the chart_access  id 
        $access_chart_id = $banking_request->access_chart->id;


        // get the access of the  chart
        $access_chart = AccessChart::where('name', '=', 'Online Banking')->get()->first();

        // count the record from database
        $approver_ctr = AccessChart::where('id', '=', $access_chart_id)
            ->with('access_chart_user_maps')
            ->get()
            ->first()
            ->access_chart_user_maps
            ->where('user_id', '=', Auth::id())
            ->count();


        // for security filteration the the user is approver or not
        if ($approver_ctr === 0 || !$user_can_approve_banking) {
            return response()->json(['error' => "You don't have permission to do this action", $approver_ctr], 200);
        }


        // get the access level of the current approver
        $access_level = $banking_request->access_chart
            ->access_chart_user_maps
            ->where('user_id', Auth::id())
            ->first()
            ->access_level;


        // get the max lvl value of the  access chart
        $max_approval_level = AccessChart::find($access_chart_id)->max_approval_level;


        // get the required number of approvers (max level)
        $num_of_approvers_max_level = ApproverPerLevel::where('module_id', '=', $access_chart->access_for)
            ->where('level', '=', $max_approval_level)
            ->max('num_of_approvers');


        // insert a logs on the approve logs records 
        ApprovedLog::create([
            'module_id' => $this->module()->id, //Tactical Requisition
            'document_id' => $banking_request_id,
            'approver_id' => Auth::id(),
            'level' => $access_level,
        ]);


        // upate the status on the banking table
        $onlinebanking = OnlineBank::find($banking_request_id);
        $onlinebanking->approve_level = $access_level + 1;
        $onlinebanking->status = 'On Process';
        $onlinebanking->save();

        // get the inserted data  from approved logs
        $approved_logs = ApprovedLog::where('document_id', '=', $banking_request_id)
            ->get();


        // get the number of approvers (maximum level)
        $approved_logs_max_level = $approved_logs->where('level', '=', $max_approval_level)->count();


        //approval logs of maximum level is equal or greater than required number of approval then update status 
        if ($approved_logs_max_level === $num_of_approvers_max_level) {


            OnlineBank::where('id', '=', $banking_request_id)
                ->update(['status' => 'Approved', 'date_approve' => $date_now]);
        }

        $this->activity_log($banking_request_id, 'approve');

        return $approved_logs;

        return response()->json([
            'success' => 'Record has been approved',
            'approved_logs' => $approved_logs,
        ], 200);
    }

    public function disapprove(Request $request)
    {
        $rules = [
            'id' => 'required',
            'remarks' => 'required',

        ];

        $validator = Validator::make(
            $request->all(),
            $rules,
            [
                'id.required' => 'No request has been submitted.',
                'remarks.required' => 'Please provide remarks when disapproving a request.'
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        $online_banking = OnlineBank::find($request->id);

        if (!$online_banking) {
            return response()->json(['success' => false, 'status' => 'error', 'title' => 'Request not found'], 404);
        }


        $online_banking->remarks = $request->get('remarks');
        $online_banking->approve_level = 0;
        $online_banking->status = 'Disapproved';
        $online_banking->save();

        // insert a logs on the approve logs records 
        ApprovedLog::create([
            'module_id' => $this->module()->id, //Tactical Requisition
            'document_id' => $online_banking->id,
            'approver_id' => Auth::id(),
            'level' => 0,
        ]);

        return response()->json([
            'success' => true,
            'status' => 'success',
            'title' => 'The request has been disapproved',
            'text' => 'Disapproved successfully.',
            'online_banking' => $online_banking
        ]);
    }

    public function activity_log($id, $description)
    {

        // create activity log for Tactical Requisition
        $log = new ModuleActivityLog();
        $log->module_id = $this->module()->id;
        $log->document_id = $id;
        $log->user_id = Auth::id();
        $log->description = $description;
        $log->save();

        return $log;
    }

    public function delete(Request $request)
    {

        $banking_request = OnlineBank::find($request->id);

        if (empty($banking_request->id)) {
            return abort(404, 'Not Found');
        }

        $approve_logs = ApprovedLog::where('document_id',  $request->id)->get();

        foreach ($approve_logs as $logs) {
            $logs->delete();
        }

        $banking_request->delete();

        return response()->json([
            'success' => true,
            'status' => 'success',
            'title' => 'The request has been deleted',
            'text' => 'Deleted successfully.',
        ], 200);

    }
}

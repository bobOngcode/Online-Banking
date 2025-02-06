<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OnlineBank extends Model
{
    protected $table = 'online_banks';
    protected $fillable = [
        'access_module_id',
        'user_id',
        'formtype',
        'submitted_at',
        'company',
        'position',
        'name',
        'bdate',
        'email',
        'contact',
        'tin',
        'sss',
        'philhealth',
        'update_type',
        'from_acct',
        'to_acct',
        'bank',
        'auth_id',
        'bank_name',
        'acct_no',
        'check_series',
        'amount_limit',
        'allowed_access1',
        'allowed_access2',
        'date_started',
        'date_ended',
        'status',
        'approve_level',
    ];


    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
        //                     (<Model>, <id_of_specified_Model>, id_of_this_model>)
    }


    public function access_module()
    {
        return $this->belongsTo('App\AccessModule', 'access_module_id', 'id');
        //                     (<Model>, <id_of_specified_Model>, id_of_this_model>)
    }



    public function approved_logs()
    {   
        return $this->hasMany('App\ApprovedLog', 'document_id', 'id');
        //                   (<Model>, <id_of_specified_Model>, <id_of_this_model>)
    }




    public function access_chart()
    {
        return $this->hasone('App\AccessChart', 'access_for', 'access_module_id');
        //                     (<Model>, <id_of_specified_Model>, id_of_this_model>)
    }
}
